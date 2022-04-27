<?php
declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;

/**
 * @implements ZonedDateTimeInterface<TimeZoneRegion>
 */
class UTCDateTime implements ZonedDateTimeInterface
{
    private function __construct(private RegionZonedDateTime $zonedDateTime)
    {
        if (!$zonedDateTime->getTimeZone()->isEqualTo(TimeZoneRegion::utc())) {
            throw new \Exception(sprintf('Timezone of class "%s" must be UTC. "%s" given', self::class, $zonedDateTime->getTimeZone()->getId()));
        }
    }

    public function __toString(): string
    {
        return $this->zonedDateTime->__toString();
    }

    public static function of(LocalDateTime $dateTime): self
    {
        return new self(RegionZonedDateTime::of($dateTime, TimeZoneRegion::utc()));
    }

    public static function parse(string $text, ?DateTimeParser $parser = null): self
    {
        return new self(RegionZonedDateTime::parse($text, $parser));
    }

    public static function parseAndConvert(string $text, ?DateTimeParser $parser = null): self
    {
        return new self(RegionZonedDateTime::parse($text, $parser)->withTimeZoneSameInstant(TimeZoneRegion::utc()));
    }

    public function toZonedDateTime(): ZonedDateTime
    {
        return ZonedDateTime::of($this->zonedDateTime->getDateTime(), $this->zonedDateTime->getTimeZone());
    }

    public function toRegionZonedDateTime(): RegionZonedDateTime
    {
        return RegionZonedDateTime::of($this->zonedDateTime->getDateTime(), $this->zonedDateTime->getTimeZone());
    }

    public static function ofInstant(Instant $instant): self
    {
        return new self(RegionZonedDateTime::ofInstant($instant, TimeZoneRegion::utc()));
    }

    public static function now(?Clock $clock = null): self
    {
        return new self(RegionZonedDateTime::now( TimeZoneRegion::utc()));
    }

    public static function from(DateTimeParseResult $result): self
    {
        return new self(RegionZonedDateTime::from($result));
    }

    public static function fromAndConvert(DateTimeParseResult $result): self
    {
        return new self(RegionZonedDateTime::from($result)->withTimeZoneSameInstant(TimeZoneRegion::utc()));
    }

    public static function fromDateTime(\DateTimeInterface $dateTime): self
    {
        return new self(RegionZonedDateTime::fromDateTime($dateTime));
    }

    public static function fromDateTimeAndConvert(\DateTimeInterface $dateTime): self
    {
        return new self(RegionZonedDateTime::fromDateTime($dateTime)->withTimeZoneSameInstant(TimeZoneRegion::utc()));
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

    /**
     * Returns the time-zone, region or offset.
     */
    public function getTimeZone(): TimeZone
    {
        return $this->zonedDateTime->getTimeZone();
    }

    /**
     * Returns the time-zone offset.
     */
    public function getTimeZoneOffset(): TimeZoneOffset
    {
        return $this->zonedDateTime->getTimeZoneOffset();
    }

    public function getInstant(): Instant
    {
        return $this->zonedDateTime->getInstant();
    }

    /**
     * Returns a copy of this ZonedDateTime with a different date.
     */
    public function withDate(LocalDate $date): self
    {
        return new self($this->zonedDateTime->withDate($date));
    }

    /**
     * Returns a copy of this ZonedDateTime with a different time.
     */
    public function withTime(LocalTime $time): self
    {
        return new self($this->zonedDateTime->withTime($time));
    }

    /**
     * Returns a copy of this ZonedDateTime with the year altered.
     */
    public function withYear(int $year): self
    {
        return  new self($this->zonedDateTime->withYear($year));
    }

    /**
     * Returns a copy of this ZonedDateTime with the month-of-year altered.
     */
    public function withMonth(int $month): self
    {
        return new self($this->zonedDateTime->withMonth($month));
    }

    /**
     * Returns a copy of this ZonedDateTime with the day-of-month altered.
     */
    public function withDay(int $day): self
    {
        return  new self($this->zonedDateTime->withDay($day));
    }

    /**
     * Returns a copy of this ZonedDateTime with the hour-of-day altered.
     */
    public function withHour(int $hour): self
    {
        return  new self($this->zonedDateTime->withHour($hour));
    }

    /**
     * Returns a copy of this ZonedDateTime with the minute-of-hour altered.
     */
    public function withMinute(int $minute): self
    {
        return  new self($this->zonedDateTime->withMinute($minute));
    }

    /**
     * Returns a copy of this ZonedDateTime with the second-of-minute altered.
     */
    public function withSecond(int $second): self
    {
        return  new self($this->zonedDateTime->withSecond($second));
    }

    /**
     * Returns a copy of this ZonedDateTime with the nano-of-second altered.
     */
    public function withNano(int $nano): self
    {
        return  new self($this->zonedDateTime->withNano($nano));
    }

    /**
     * Returns a copy of this `ZonedDateTime` with a different time-zone,
     * retaining the local date-time if possible.
     */
    public function withTimeZoneSameLocal(TimeZone $timeZone): ZonedDateTimeInterface
    {
        return new self($this->zonedDateTime->withTimeZoneSameLocal($timeZone));
    }

    /**
     * Returns a copy of this date-time with a different time-zone, retaining the instant.
     */
    public function withTimeZoneSameInstant(TimeZone $timeZone): ZonedDateTimeInterface
    {
        return $this->zonedDateTime->withTimeZoneSameInstant($timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified Period added.
     */
    public function plusPeriod(Period $period): self
    {
        return new self($this->zonedDateTime->plusPeriod($period));
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified Duration added.
     */
    public function plusDuration(Duration $duration): self
    {
        return new self($this->zonedDateTime->plusDuration($duration));
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in years added.
     */
    public function plusYears(int $years): self
    {
        return new self($this->zonedDateTime->plusYears($years));
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in months added.
     */
    public function plusMonths(int $months): self
    {
        return new self($this->zonedDateTime->plusMonths($months));
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in weeks added.
     */
    public function plusWeeks(int $weeks): self
    {
        return new self($this->zonedDateTime->plusWeeks($weeks));
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in days added.
     */
    public function plusDays(int $days): self
    {
        return new self($this->zonedDateTime->plusDays($days));
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in hours added.
     */
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

    public function compareTo( ZonedDateTimeInterface $that): int
    {
        return $this->zonedDateTime->compareTo($that);
    }

    public function isEqualTo( ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isEqualTo($that);
    }

    public function isAfter( ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isAfter($that);
    }

    public function isAfterOrEqualTo( ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isAfterOrEqualTo($that);
    }

    public function isBefore( ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isBefore($that);
    }

    public function isBeforeOrEqualTo( ZonedDateTimeInterface $that): bool
    {
        return $this->zonedDateTime->isBeforeOrEqualTo($that);
    }

    public function isBetweenInclusive( ZonedDateTimeInterface $from,  ZonedDateTimeInterface $to): bool
    {
        return $this->zonedDateTime->isBetweenInclusive($from, $to);
    }

    public function isBetweenExclusive( ZonedDateTimeInterface $from,  ZonedDateTimeInterface $to): bool
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
        return $this->zonedDateTime->withFixedOffsetTimeZone();
    }
}
