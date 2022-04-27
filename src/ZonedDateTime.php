<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;

/**
 * @template TTimezone of TimeZone
 * @implements ZonedDateTimeInterface<TTimezone>
 */
final class ZonedDateTime implements ZonedDateTimeInterface
{
    /**
     * The local date-time.
     */
    private LocalDateTime $localDateTime;

    /**
     * The time-zone offset from UTC/Greenwich.
     */
    private TimeZoneOffset $timeZoneOffset;

    /***
     * The time-zone.
     *
     * It is either a TimeZoneRegion if this ZonedDateTimeInterface is region-based,
     * or the same instance as the offset if this ZonedDateTimeInterface is offset-based.
     *
     * @var TTimezone $timeZone
     */
    private TimeZone $timeZone;

    /**
     * The instant represented by this ZonedDateTime.
     */
    private Instant $instant;

    /**
     * @param TTimezone $zone
     */
    public function __construct(LocalDateTime $localDateTime, TimeZoneOffset $offset, TimeZone $zone, Instant $instant)
    {
        $this->localDateTime  = $localDateTime;
        $this->timeZone       = $zone;
        $this->timeZoneOffset = $offset;
        $this->instant        = $instant;
    }

    /**
     * @param TTimezone $timeZone
     */
    public static function of(LocalDateTime $dateTime, TimeZone $timeZone) : self
    {
        $dtz = $timeZone->toDateTimeZone();
        $dt = new \DateTime((string) $dateTime->withNano(0), $dtz);

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
    public static function ofInstant(Instant $instant, TimeZone $timeZone) : self
    {
        $dateTimeZone = $timeZone->toDateTimeZone();

        // We need to pass a DateTimeZone to avoid a PHP warning...
        $dateTime = new \DateTime('@' . $instant->getEpochSecond(), $dateTimeZone);

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
    public static function now(TimeZone $timeZone, ?Clock $clock = null) : self
    {
        return ZonedDateTime::ofInstant(Instant::now($clock), $timeZone);
    }

    public static function from(DateTimeParseResult $result) : self
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
            $timeZone
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
    public static function parse(string $text, ?DateTimeParser $parser = null) : self
    {
        if (! $parser) {
            $parser = IsoParsers::zonedDateTime();
        }

        return ZonedDateTime::from($parser->parse($text));
    }

    public static function fromDateTime(\DateTimeInterface $dateTime) : self
    {
        $localDateTime = LocalDateTime::fromDateTime($dateTime);

        $dateTimeZone = $dateTime->getTimezone();

        if ($dateTimeZone === false) {
            throw new DateTimeException('This DateTime object has no timezone.');
        }

        $timeZone = TimeZone::fromDateTimeZone($dateTimeZone);

        if ($timeZone instanceof TimeZoneOffset) {
            $timeZoneOffset = $timeZone;
        } else {
            $timeZoneOffset = TimeZoneOffset::ofTotalSeconds($dateTime->getOffset());
        }

        $instant = Instant::of($dateTime->getTimestamp(), $localDateTime->getNano());

        return new ZonedDateTime($localDateTime, $timeZoneOffset, $timeZone, $instant);
    }

    public function getDateTime() : LocalDateTime
    {
        return $this->localDateTime;
    }

    public function getDate() : LocalDate
    {
        return $this->localDateTime->getDate();
    }

    public function getTime() : LocalTime
    {
        return $this->localDateTime->getTime();
    }

    public function getYear() : int
    {
        return $this->localDateTime->getYear();
    }

    public function getMonth() : int
    {
        return $this->localDateTime->getMonth();
    }

    public function getDay() : int
    {
        return $this->localDateTime->getDay();
    }

    public function getDayOfWeek() : DayOfWeek
    {
        return $this->localDateTime->getDayOfWeek();
    }

    public function getDayOfYear() : int
    {
        return $this->localDateTime->getDayOfYear();
    }

    public function getHour() : int
    {
        return $this->localDateTime->getHour();
    }

    public function getMinute() : int
    {
        return $this->localDateTime->getMinute();
    }

    public function getSecond() : int
    {
        return $this->localDateTime->getSecond();
    }

    public function getEpochSecond() : int
    {
        return $this->instant->getEpochSecond();
    }

    public function getNano() : int
    {
        return $this->instant->getNano();
    }

    public function getTimeZone() : TimeZone
    {
        return $this->timeZone;
    }

    public function getTimeZoneOffset() : TimeZoneOffset
    {
        return $this->timeZoneOffset;
    }

    public function getInstant() : Instant
    {
        return $this->instant;
    }

    public function withDate(LocalDate $date) : self
    {
        return ZonedDateTime::of($this->localDateTime->withDate($date), $this->timeZone);
    }

    public function withTime(LocalTime $time) : self
    {
        return ZonedDateTime::of($this->localDateTime->withTime($time), $this->timeZone);
    }

    public function withYear(int $year) : self
    {
        return ZonedDateTime::of($this->localDateTime->withYear($year), $this->timeZone);
    }

    public function withMonth(int $month) : self
    {
        return ZonedDateTime::of($this->localDateTime->withMonth($month), $this->timeZone);
    }

    public function withDay(int $day) : self
    {
        return ZonedDateTime::of($this->localDateTime->withDay($day), $this->timeZone);
    }

    public function withHour(int $hour) : self
    {
        return ZonedDateTime::of($this->localDateTime->withHour($hour), $this->timeZone);
    }

    public function withMinute(int $minute) : self
    {
        return ZonedDateTime::of($this->localDateTime->withMinute($minute), $this->timeZone);
    }

    public function withSecond(int $second) : self
    {
        return ZonedDateTime::of($this->localDateTime->withSecond($second), $this->timeZone);
    }

    public function withNano(int $nano) : self
    {
        return ZonedDateTime::of($this->localDateTime->withNano($nano), $this->timeZone);
    }

    public function withTimeZoneSameLocal(TimeZone $timeZone) : self
    {
        return ZonedDateTime::of($this->localDateTime, $timeZone);
    }

    public function withTimeZoneSameInstant(TimeZone $timeZone) : self
    {
        return ZonedDateTime::ofInstant($this->instant, $timeZone);
    }

    public function withFixedOffsetTimeZone() : self
    {
        return ZonedDateTime::of($this->localDateTime, $this->timeZoneOffset);
    }

    public function plusPeriod(Period $period) : self
    {
        return ZonedDateTime::of($this->localDateTime->plusPeriod($period), $this->timeZone);
    }

    public function plusDuration(Duration $duration) : self
    {
        return ZonedDateTime::ofInstant($this->instant->plus($duration), $this->timeZone);
    }

    public function plusYears(int $years) : self
    {
        return ZonedDateTime::of($this->localDateTime->plusYears($years), $this->timeZone);
    }

    public function plusMonths(int $months) : self
    {
        return ZonedDateTime::of($this->localDateTime->plusMonths($months), $this->timeZone);
    }

    public function plusWeeks(int $weeks) : self
    {
        return ZonedDateTime::of($this->localDateTime->plusWeeks($weeks), $this->timeZone);
    }

    public function plusDays(int $days) : self
    {
        return ZonedDateTime::of($this->localDateTime->plusDays($days), $this->timeZone);
    }

    public function plusHours(int $hours) : self
    {
        return ZonedDateTime::of($this->localDateTime->plusHours($hours), $this->timeZone);
    }

    public function plusMinutes(int $minutes) : self
    {
        return ZonedDateTime::of($this->localDateTime->plusMinutes($minutes), $this->timeZone);
    }

    public function plusSeconds(int $seconds) : self
    {
        return ZonedDateTime::of($this->localDateTime->plusSeconds($seconds), $this->timeZone);
    }

    public function minusPeriod(Period $period) : self
    {
        return $this->plusPeriod($period->negated());
    }

    public function minusDuration(Duration $duration) : self
    {
        return $this->plusDuration($duration->negated());
    }

    public function minusYears(int $years) : self
    {
        return $this->plusYears(- $years);
    }

    public function minusMonths(int $months) : self
    {
        return $this->plusMonths(- $months);
    }

    public function minusWeeks(int $weeks) : self
    {
        return $this->plusWeeks(- $weeks);
    }

    public function minusDays(int $days) : self
    {
        return $this->plusDays(- $days);
    }

    public function minusHours(int $hours) : self
    {
        return $this->plusHours(- $hours);
    }

    public function minusMinutes(int $minutes) : self
    {
        return $this->plusMinutes(- $minutes);
    }

    public function minusSeconds(int $seconds) : self
    {
        return $this->plusSeconds(- $seconds);
    }

    public function compareTo(ZonedDateTimeInterface $that) : int
    {
        return $this->instant->compareTo($that->instant);
    }

    public function isEqualTo(ZonedDateTimeInterface $that) : bool
    {
        return $this->compareTo($that) === 0;
    }

    public function isAfter(ZonedDateTimeInterface $that) : bool
    {
        return $this->compareTo($that) === 1;
    }

    public function isAfterOrEqualTo(ZonedDateTimeInterface $that) : bool
    {
        return $this->compareTo($that) >= 0;
    }

    public function isBefore(ZonedDateTimeInterface $that) : bool
    {
        return $this->compareTo($that) === -1;
    }

    public function isBeforeOrEqualTo(ZonedDateTimeInterface $that) : bool
    {
        return $this->compareTo($that) <= 0;
    }

    public function isBetweenInclusive(ZonedDateTimeInterface $from, ZonedDateTimeInterface $to) : bool
    {
        return $this->isAfterOrEqualTo($from) && $this->isBeforeOrEqualTo($to);
    }

    public function isBetweenExclusive(ZonedDateTimeInterface $from, ZonedDateTimeInterface $to) : bool
    {
        return $this->isAfter($from) && $this->isBefore($to);
    }

    public function isFuture(?Clock $clock = null) : bool
    {
        return $this->instant->isFuture($clock);
    }

    public function isPast(?Clock $clock = null) : bool
    {
        return $this->instant->isPast($clock);
    }

    public function toDateTime() : \DateTime
    {
        $second = $this->localDateTime->getSecond();

        // round down to the microsecond
        $nano = $this->localDateTime->getNano();
        $nano = 1000 * intdiv($nano, 1000);

        $dateTime = (string) $this->localDateTime->withNano($nano);
        $dateTimeZone = $this->timeZone->toDateTimeZone();

        $format = 'Y-m-d\TH:i';

        if ($second !== 0 || $nano !== 0) {
            $format .= ':s';

            if ($nano !== 0) {
                $format .= '.u';
            }
        }

        return \DateTime::createFromFormat($format, $dateTime, $dateTimeZone);
    }

    public function toDateTimeImmutable() : \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable($this->toDateTime());
    }

    public function jsonSerialize() : string
    {
        return (string) $this;
    }

    public function __toString() : string
    {
        $string = $this->localDateTime . $this->timeZoneOffset;

        if ($this->timeZone instanceof TimeZoneRegion) {
            $string .= '[' . $this->timeZone . ']';
        }

        return $string;
    }
}
