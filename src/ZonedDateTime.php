<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Stringable;

use function assert;
use function intdiv;

/**
 * @template TTimezone of TimeZone
 * @implements ZonedDateTimeInterface<TTimezone>
 */
class ZonedDateTime implements ZonedDateTimeInterface
{
    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * @param LocalDateTime  $localDateTime  The local date-time.
     * @param TimeZoneOffset $timeZoneOffset The time-zone offset from UTC/Greenwich.
     * @param TTimezone       $timeZone       The time-zone. It is either a TimeZoneRegion if this ZonedDateTime is
     *                                       region-based, or the same instance as the offset if this ZonedDateTime
     *                                       is offset-based.
     * @param Instant        $instant        The instant represented by this ZonedDateTime.
     */
    public function __construct(
        private readonly LocalDateTime $localDateTime,
        private readonly TimeZoneOffset $timeZoneOffset,
        private readonly TimeZone $timeZone,
        private readonly Instant $instant,
    ) {
    }

    /**
     * Creates a ZonedDateTime from a LocalDateTime and a TimeZone.
     *
     * This resolves the local date-time to an instant on the time-line.
     *
     * When a TimeZoneOffset is used, the local date-time can be converted to an instant without ambiguity.
     *
     * When a TimeZoneRegion is used, Daylight Saving Time can make the conversion more complex.
     * There are 3 cases:
     *
     * - Normal: when there is only one valid offset for the date-time. The conversion is then as straightforward
     *   as when using a TimeZoneOffset. This is fortunately the case for the vast majority of the year.
     * - Gap: when there is no valid offset for the date-time. This happens when the clock jumps forward
     *   typically due to a DST transition from "winter" to "summer". The date-times between the two times
     *   of the transition do not exist.
     * - Overlap: when there are two valid offsets for the date-time. This happens when the clock is set back
     *   typically due to a DST transition from "summer" to "winter". The date-times between the two times
     *   of the transition can be resolved to two different offsets, representing two different instants
     *   on the time-line.
     *
     * The strategy for resolving gaps and overlaps is the following:
     *
     * - If the local date-time falls in the middle of a gap, then the resulting date-time will be shifted forward
     *   by the length of the gap, and the later offset, typically "summer" time, will be used.
     * - If the local date-time falls in the middle of an overlap, then the offset closest to UTC will be used.
     *
     * @param TTimezone $timeZone
     */
    public static function of(LocalDateTime $dateTime, TimeZone $timeZone): self
    {
        $dtz = $timeZone->toNativeDateTimeZone();
        $dt = new DateTime((string) $dateTime->withNano(0), $dtz);

        $instant = Instant::of($dt->getTimestamp(), $dateTime->getNano());

        if ($timeZone instanceof TimeZoneOffset) {
            $timeZoneOffset = $timeZone;
        } else {
            $timeZoneOffset = TimeZoneOffset::ofTotalSeconds($dt->getOffset());
        }

        // The time can be affected if the date-time is not valid for the given time-zone due to a DST transition,
        // so we have to re-compute the local date-time from the DateTime object.
        // DateTime does not support nanos of seconds, so we just copy the nanos back from the original date-time.
        $dateTime = LocalDateTime::parse($dt->format('Y-m-d\TH:i:s'))->withNano($dateTime->getNano());

        return new ZonedDateTime($dateTime, $timeZoneOffset, $timeZone, $instant);
    }

    /**
     * @param TTimezone $timeZone
     */
    public static function ofInstant(Instant $instant, TimeZone $timeZone): self
    {
        $dateTimeZone = $timeZone->toNativeDateTimeZone();

        // We need to pass a DateTimeZone to avoid a PHP warning...
        $dateTime = new DateTime('@' . $instant->getEpochSecond(), $dateTimeZone);

        // ... but this DateTimeZone is ignored because of the timestamp, so we set it again.
        $dateTime->setTimezone($dateTimeZone);

        $localDateTime = LocalDateTime::parse($dateTime->format('Y-m-d\TH:i:s'));
        $localDateTime = $localDateTime->withNano($instant->getNano());

        if ($timeZone instanceof TimeZoneOffset) {
            $timeZoneOffset = $timeZone;
        } else {
            $timeZoneOffset = TimeZoneOffset::ofTotalSeconds($dateTime->getOffset());
        }

        return new ZonedDateTime($localDateTime, $timeZoneOffset, $timeZone, $instant);
    }

    /**
     * @param TTimezone $timeZone
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): self
    {
        return ZonedDateTime::ofInstant(Instant::now($clock), $timeZone);
    }

    public static function from(DateTimeParseResult $result): self
    {
        $localDateTime = LocalDateTime::from($result);

        $timeZoneOffset = TimeZoneOffset::from($result);

        if ($result->hasField(Field\TimeZoneRegion::NAME)) {
            $timeZone = TimeZoneRegion::from($result);
        } else {
            $timeZone = $timeZoneOffset;
        }

        return ZonedDateTime::of(
            $localDateTime,
            $timeZone,
        );
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a text string.
     *
     * Valid examples:
     * - `2007-12-03T10:15:30:45Z`
     * - `2007-12-03T10:15:30+01:00`
     * - `2007-12-03T10:15:30+01:00[Europe/Paris]`
     *
     * @param string              $text   The text to parse.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): self
    {
        if ($parser === null) {
            $parser = IsoParsers::zonedDateTime();
        }

        return ZonedDateTime::from($parser->parse($text));
    }

    public static function fromNativeDateTime(DateTimeInterface $dateTime): self
    {
        $localDateTime = LocalDateTime::fromNativeDateTime($dateTime);

        $dateTimeZone = $dateTime->getTimezone();

        if ($dateTimeZone === false) {
            // @codeCoverageIgnoreStart
            throw new DateTimeException('This DateTime object has no timezone.');
            // @codeCoverageIgnoreEnd
        }

        $timeZone = TimeZone::fromNativeDateTimeZone($dateTimeZone);

        if ($timeZone instanceof TimeZoneOffset) {
            $timeZoneOffset = $timeZone;
        } else {
            $timeZoneOffset = TimeZoneOffset::ofTotalSeconds($dateTime->getOffset());
        }

        $instant = Instant::of($dateTime->getTimestamp(), $localDateTime->getNano());

        return new ZonedDateTime($localDateTime, $timeZoneOffset, $timeZone, $instant);
    }

    public function getDateTime(): LocalDateTime
    {
        return $this->localDateTime;
    }

    public function getDate(): LocalDate
    {
        return $this->localDateTime->getDate();
    }

    public function getTime(): LocalTime
    {
        return $this->localDateTime->getTime();
    }

    public function getYear(): int
    {
        return $this->localDateTime->getYear();
    }

    /**
     * Returns the month-of-year as a Month enum.
     */
    public function getMonth(): Month
    {
        return $this->localDateTime->getMonth();
    }

    /**
     * Returns the month-of-year value from 1 to 12.
     *
     * @return int<1, 12>
     */
    public function getMonthValue(): int
    {
        return $this->localDateTime->getMonthValue();
    }

    /**
     * @return int<1, 31>
     */
    public function getDayOfMonth(): int
    {
        return $this->localDateTime->getDayOfMonth();
    }

    public function getDayOfWeek(): DayOfWeek
    {
        return $this->localDateTime->getDayOfWeek();
    }

    /**
     * @return int<1, 366>
     */
    public function getDayOfYear(): int
    {
        return $this->localDateTime->getDayOfYear();
    }

    public function getHour(): int
    {
        return $this->localDateTime->getHour();
    }

    public function getMinute(): int
    {
        return $this->localDateTime->getMinute();
    }

    public function getSecond(): int
    {
        return $this->localDateTime->getSecond();
    }

    public function getEpochSecond(): int
    {
        return $this->instant->getEpochSecond();
    }

    public function getNano(): int
    {
        return $this->instant->getNano();
    }

    public function getTimeZone(): TimeZone
    {
        return $this->timeZone;
    }

    public function getTimeZoneOffset(): TimeZoneOffset
    {
        return $this->timeZoneOffset;
    }

    public function getInstant(): Instant
    {
        return $this->instant;
    }

    public function withDate(LocalDate $date): self
    {
        return ZonedDateTime::of($this->localDateTime->withDate($date), $this->timeZone);
    }

    public function withTime(LocalTime $time): self
    {
        return ZonedDateTime::of($this->localDateTime->withTime($time), $this->timeZone);
    }

    public function withYear(int $year): self
    {
        return ZonedDateTime::of($this->localDateTime->withYear($year), $this->timeZone);
    }

    public function withMonth(int|Month $month): self
    {
        return ZonedDateTime::of($this->localDateTime->withMonth($month), $this->timeZone);
    }

    public function withDay(int $day): self
    {
        return ZonedDateTime::of($this->localDateTime->withDay($day), $this->timeZone);
    }

    public function withHour(int $hour): self
    {
        return ZonedDateTime::of($this->localDateTime->withHour($hour), $this->timeZone);
    }

    public function withMinute(int $minute): self
    {
        return ZonedDateTime::of($this->localDateTime->withMinute($minute), $this->timeZone);
    }

    public function withSecond(int $second): self
    {
        return ZonedDateTime::of($this->localDateTime->withSecond($second), $this->timeZone);
    }

    public function withNano(int $nano): self
    {
        return ZonedDateTime::of($this->localDateTime->withNano($nano), $this->timeZone);
    }

    public function withTimeZoneSameLocal(TimeZone $timeZone): self
    {
        return ZonedDateTime::of($this->localDateTime, $timeZone);
    }

    public function withTimeZoneSameInstant(TimeZone $timeZone): self
    {
        return ZonedDateTime::ofInstant($this->instant, $timeZone);
    }

    public function withFixedOffsetTimeZone(): self
    {
        return ZonedDateTime::of($this->localDateTime, $this->timeZoneOffset);
    }

    public function plusPeriod(Period $period): self
    {
        return ZonedDateTime::of($this->localDateTime->plusPeriod($period), $this->timeZone);
    }

    public function plusDuration(Duration $duration): self
    {
        return ZonedDateTime::ofInstant($this->instant->plus($duration), $this->timeZone);
    }

    /**
     * Returns an Interval from this ZonedDateTime (inclusive) to the given one (exclusive).
     *
     * @throws DateTimeException If the given ZonedDateTime is before this ZonedDateTime.
     */
    public function getIntervalTo(ZonedDateTime $that): Interval
    {
        return $this->getInstant()->getIntervalTo($that->getInstant());
    }

    /**
     * Returns a Duration representing the time elapsed between this ZonedDateTime and the given one.
     * This method will return a negative duration if the given ZonedDateTime is before the current one.
     */
    public function getDurationTo(ZonedDateTime $that): Duration
    {
        return Duration::between($this->getInstant(), $that->getInstant());
    }

    public function plusYears(int $years) : self
    {
        return ZonedDateTime::of($this->localDateTime->plusYears($years), $this->timeZone);
    }

    public function plusMonths(int $months): self
    {
        return ZonedDateTime::of($this->localDateTime->plusMonths($months), $this->timeZone);
    }

    public function plusWeeks(int $weeks): self
    {
        return ZonedDateTime::of($this->localDateTime->plusWeeks($weeks), $this->timeZone);
    }

    public function plusDays(int $days): self
    {
        return ZonedDateTime::of($this->localDateTime->plusDays($days), $this->timeZone);
    }

    public function plusHours(int $hours): self
    {
        return ZonedDateTime::of($this->localDateTime->plusHours($hours), $this->timeZone);
    }

    public function plusMinutes(int $minutes): self
    {
        return ZonedDateTime::of($this->localDateTime->plusMinutes($minutes), $this->timeZone);
    }

    public function plusSeconds(int $seconds): self
    {
        return ZonedDateTime::of($this->localDateTime->plusSeconds($seconds), $this->timeZone);
    }

    public function minusPeriod(Period $period): self
    {
        return $this->plusPeriod($period->negated());
    }

    public function minusDuration(Duration $duration): self
    {
        return $this->plusDuration($duration->negated());
    }

    public function minusYears(int $years): self
    {
        return $this->plusYears(-$years);
    }

    public function minusMonths(int $months): self
    {
        return $this->plusMonths(-$months);
    }

    public function minusWeeks(int $weeks): self
    {
        return $this->plusWeeks(-$weeks);
    }

    public function minusDays(int $days): self
    {
        return $this->plusDays(-$days);
    }

    public function minusHours(int $hours): self
    {
        return $this->plusHours(-$hours);
    }

    public function minusMinutes(int $minutes): self
    {
        return $this->plusMinutes(-$minutes);
    }

    public function minusSeconds(int $seconds): self
    {
        return $this->plusSeconds(-$seconds);
    }

    /**
     * Compares this ZonedDateTime with another.
     *
     * The comparison is performed on the instant.
     *
     * @return int [-1,0,1] If this zoned date-time is before, on, or after the given one.
     *
     * @psalm-return -1|0|1
     */
    public function compareTo(ZonedDateTimeInterface $that) : int
    {
        return $this->instant->compareTo($that->instant);
    }

    public function isEqualTo(ZonedDateTimeInterface $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    public function isAfter(ZonedDateTimeInterface $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    public function isAfterOrEqualTo(ZonedDateTimeInterface $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    public function isBefore(ZonedDateTimeInterface $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    public function isBeforeOrEqualTo(ZonedDateTimeInterface $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    public function isBetweenInclusive(ZonedDateTimeInterface $from, ZonedDateTimeInterface $to): bool
    {
        return $this->isAfterOrEqualTo($from) && $this->isBeforeOrEqualTo($to);
    }

    public function isBetweenExclusive(ZonedDateTimeInterface $from, ZonedDateTimeInterface $to): bool
    {
        return $this->isAfter($from) && $this->isBefore($to);
    }

    public function isFuture(?Clock $clock = null): bool
    {
        return $this->instant->isFuture($clock);
    }

    public function isPast(?Clock $clock = null): bool
    {
        return $this->instant->isPast($clock);
    }

    public function toNativeDateTime(): DateTime
    {
        $second = $this->localDateTime->getSecond();

        // round down to the microsecond
        $nano = $this->localDateTime->getNano();
        $nano = 1000 * intdiv($nano, 1000);

        $dateTime = (string) $this->localDateTime->withNano($nano);
        $dateTimeZone = $this->timeZone->toNativeDateTimeZone();

        $format = 'Y-m-d\TH:i';

        if ($second !== 0 || $nano !== 0) {
            $format .= ':s';

            if ($nano !== 0) {
                $format .= '.u';
            }
        }

        $nativeDateTime = DateTime::createFromFormat($format, $dateTime, $dateTimeZone);

        assert($nativeDateTime !== false);

        return $nativeDateTime;
    }

    public function toNativeDateTimeImmutable(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->toNativeDateTime());
    }

    /**
     * Serializes as a string using {@see ZonedDateTime::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this zoned date time.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        $string = $this->localDateTime . $this->timeZoneOffset;

        if ($this->timeZone instanceof TimeZoneRegion) {
            $string .= '[' . $this->timeZone . ']';
        }

        return $string;
    }

    /**
     * {@see ZonedDateTime::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
