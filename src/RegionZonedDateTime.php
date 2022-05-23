<?php
declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;

/**
 * @implements ZonedDateTimeInterface<TimeZoneRegion>
 */
class RegionZonedDateTime implements ZonedDateTimeInterface
{
    private function __construct(private ZonedDateTimeInterface $zonedDateTime)
    {
        if (!$zonedDateTime->getTimeZone() instanceof TimeZoneRegion) {
            throw new \Exception(sprintf('Timezone of class "%s" must be an instance of "%s". "%s" given with id "%s', self::class, TimeZoneRegion::class, $zonedDateTime->getTimeZone()::class, $zonedDateTime->getTimeZone()->getId()));
        }
    }

    public function __toString(): string
    {
        return $this->zonedDateTime->__toString();
    }

    public function toUTCDateTime(): UTCDateTime
    {
        return UTCDateTime::of($this->zonedDateTime->withTimeZoneSameInstant(TimeZoneRegion::utc())->getDateTime());
    }

    public static function fromZonedDateTime(ZonedDateTime $zonedDateTime): self
    {
        return new self($zonedDateTime);
    }

    public static function of(LocalDateTime $dateTime, TimeZoneRegion $timeZone): self
    {
        return new self(ZonedDateTime::of($dateTime, $timeZone));
    }

    public static function parse(string $text, ?DateTimeParser $parser = null): self
    {
        $parsedZonedDateTime = ZonedDateTime::parse($text, $parser);

        if ($parsedZonedDateTime->getTimeZoneOffset()->isEqualTo(TimeZoneOffset::utc())) {
            $parsedZonedDateTime->withTimeZoneSameInstant(TimeZoneRegion::utc());
        }

        if(!$parsedZonedDateTime->getTimeZone() instanceof TimeZoneRegion) {
            throw new DateTimeParseException(sprintf('"%s" must have a timezone region. None found while parsing "%s"', self::class, $text));
        }

        return new self($parsedZonedDateTime);
    }
    
    public static function ofInstant(Instant $instant, TimeZoneRegion $timeZone): self
    {
        return new self(ZonedDateTime::ofInstant($instant, $timeZone));
    }

    public static function now(TimeZone $timeZone, ?Clock $clock = null): self
    {
        return new self(ZonedDateTime::now( $timeZone, $clock));
    }

    public static function from(DateTimeParseResult $result): self
    {
        $parsedZonedDateTime = ZonedDateTime::from($result);

        if(!$parsedZonedDateTime->getTimeZone() instanceof TimeZoneRegion) {
            throw new DateTimeParseException(sprintf('"%s" must have a timezone region. None found while parsing "%s"', self::class, $text));
        }

        return new self($parsedZonedDateTime);
    }

    public static function fromDateTime(\DateTimeInterface $dateTime): self
    {
        return new self(ZonedDateTime::fromDateTime($dateTime));
    }

    public function getDateTime(): LocalDateTime
    {
        return $this->zonedDateTime->getDateTime();
    }

    public function getDate(): LocalDate
    {
        return $this->zonedDateTime->getDate();
    }

    public function getTime(): LocalTime
    {
        return $this->zonedDateTime->getTime();
    }

    public function getYear(): int
    {
        return $this->zonedDateTime->getYear();
    }

    public function getMonth(): int
    {
        return $this->zonedDateTime->getMonth();
    }

    public function getDay(): int
    {
        return $this->zonedDateTime->getDay();
    }

    public function getDayOfWeek(): DayOfWeek
    {
        return $this->zonedDateTime->getDayOfWeek();
    }

    public function getDayOfYear(): int
    {
        return $this->zonedDateTime->getDayOfYear();
    }

    public function getHour(): int
    {
        return $this->zonedDateTime->getHour();
    }

    public function getMinute(): int
    {
        return $this->zonedDateTime->getMinute();
    }

    public function getSecond(): int
    {
        return $this->zonedDateTime->getSecond();
    }

    public function getEpochSecond(): int
    {
        return $this->zonedDateTime->getEpochSecond();
    }

    public function getNano(): int
    {
        return $this->zonedDateTime->getNano();
    }

    public function getTimeZone(): TimeZone
    {
        return $this->zonedDateTime->getTimeZone();
    }

    public function getTimeZoneOffset(): TimeZoneOffset
    {
        return $this->zonedDateTime->getTimeZoneOffset();
    }

    public function getInstant(): Instant
    {
        return $this->zonedDateTime->getInstant();
    }

    public function withDate(LocalDate $date): self
    {
        return new self($this->zonedDateTime->withDate($date));
    }

    public function withTime(LocalTime $time): self
    {
        return new self($this->zonedDateTime->withTime($time));
    }

    public function withYear(int $year): self
    {
        return  new self($this->zonedDateTime->withYear($year));
    }

    public function withMonth(int $month): self
    {
        return new self($this->zonedDateTime->withMonth($month));
    }

    public function withDay(int $day): self
    {
        return  new self($this->zonedDateTime->withDay($day));
    }

    public function withHour(int $hour): self
    {
        return  new self($this->zonedDateTime->withHour($hour));
    }

    public function withMinute(int $minute): self
    {
        return  new self($this->zonedDateTime->withMinute($minute));
    }

    public function withSecond(int $second): self
    {
        return  new self($this->zonedDateTime->withSecond($second));
    }

    public function withNano(int $nano): self
    {
        return  new self($this->zonedDateTime->withNano($nano));
    }

    public function withTimeZoneSameLocal(TimeZone $timeZone): self
    {
        return new self($this->zonedDateTime->withTimeZoneSameLocal($timeZone));
    }

    public function withTimeZoneSameInstant(TimeZone $timeZone): self
    {
        return new self($this->zonedDateTime->withTimeZoneSameInstant($timeZone));
    }

    public function plusPeriod(Period $period): self
    {
        return new self($this->zonedDateTime->plusPeriod($period));
    }

    public function plusDuration(Duration $duration): self
    {
        return new self($this->zonedDateTime->plusDuration($duration));
    }

    public function plusYears(int $years): self
    {
        return new self($this->zonedDateTime->plusYears($years));
    }

    public function plusMonths(int $months): self
    {
        return new self($this->zonedDateTime->plusMonths($months));
    }

    public function plusWeeks(int $weeks): self
    {
        return new self($this->zonedDateTime->plusWeeks($weeks));
    }

    public function plusDays(int $days): self
    {
        return new self($this->zonedDateTime->plusDays($days));
    }

    public function plusHours(int $hours): self
    {
        return new self($this->zonedDateTime->plusHours($hours));
    }

    public function plusMinutes(int $minutes): self
    {
        return new self($this->zonedDateTime->plusMinutes($minutes));
    }

    public function plusSeconds(int $seconds): self
    {
        return new self($this->zonedDateTime->plusSeconds($seconds));
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

    public function compareTo(ZonedDateTimeInterface $that): int
    {
        return $this->zonedDateTime->compareTo($that);
    }

    public function isEqualTo(ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isEqualTo($that);
    }

    public function isAfter(ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isAfter($that);
    }

    public function isAfterOrEqualTo(ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isAfterOrEqualTo($that);
    }

    public function isBefore(ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isBefore($that);
    }

    public function isBeforeOrEqualTo(ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isBeforeOrEqualTo($that);
    }

    public function isBetweenInclusive(ZonedDateTimeInterface $from, ZonedDateTimeInterface $to): bool
    {
        return $this->zonedDateTime->isBetweenInclusive($from, $to);
    }

    public function isBetweenExclusive(ZonedDateTimeInterface $from, ZonedDateTimeInterface $to): bool
    {
        return $this->zonedDateTime->isBetweenExclusive($from, $to);
    }

    public function isFuture(?Clock $clock = null): bool
    {
        return $this->zonedDateTime->isFuture($clock);
    }

    public function isPast(?Clock $clock = null): bool
    {
        return $this->zonedDateTime->isPast($clock);
    }

    public function toDateTime(): \DateTime
    {
        return $this->zonedDateTime->toDateTime();
    }

    public function toDateTimeImmutable(): \DateTimeImmutable
    {
        return $this->zonedDateTime->toDateTimeImmutable();
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    public function withFixedOffsetTimeZone(): ZonedDateTimeInterface
    {
        return new self($this->zonedDateTime->withFixedOffsetTimeZone());
    }
}
