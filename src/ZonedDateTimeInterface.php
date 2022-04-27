<?php

namespace Brick\DateTime;


use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParseResult;

/**
 * A date-time with a time-zone in the ISO-8601 calendar system.
 *
 * A ZonedDateTimeInterface can be viewed as a LocalDateTime along with a time zone
 * and targets a specific point in time.
 *
 * @template TTimezone of Timezone
 */
interface ZonedDateTimeInterface extends \JsonSerializable
{
    /**
     * Returns the `LocalDateTime` part of this `ZonedDateTime`.
     */
    public function getDateTime(): LocalDateTime;

    /**
     * Returns the `LocalDate` part of this `ZonedDateTime`.
     */
    public function getDate(): LocalDate;

    /**
     * Returns the `LocalTime` part of this `ZonedDateTime`.
     */
    public function getTime(): LocalTime;

    public function getYear(): int;

    public function getMonth(): int;

    public function getDay(): int;

    public function getDayOfWeek(): DayOfWeek;

    public function getDayOfYear(): int;

    public function getHour(): int;

    public function getMinute(): int;

    public function getSecond(): int;

    public function getEpochSecond(): int;

    public function getNano(): int;

    /**
     * Returns the time-zone, region or offset.
     *
     * @return TTimezone
     */
    public function getTimeZone(): TimeZone;

    /**
     * Returns the time-zone offset.
     */
    public function getTimeZoneOffset(): TimeZoneOffset;

    public function getInstant(): Instant;

    /**
     * Returns a copy of this ZonedDateTimeInterface with a different date.
     */
    public function withDate(LocalDate $date): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with a different time.
     */
    public function withTime(LocalTime $time): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the year altered.
     */
    public function withYear(int $year): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the month-of-year altered.
     */
    public function withMonth(int $month): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the day-of-month altered.
     */
    public function withDay(int $day): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the hour-of-day altered.
     */
    public function withHour(int $hour): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the minute-of-hour altered.
     */
    public function withMinute(int $minute): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the second-of-minute altered.
     */
    public function withSecond(int $second): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the nano-of-second altered.
     */
    public function withNano(int $nano): self;

    /**
     * Returns a copy of this `ZonedDateTime` with a different time-zone,
     * retaining the local date-time if possible.
     */
    public function withTimeZoneSameLocal(TimeZone $timeZone): self;

    /**
     * Returns a copy of this date-time with a different time-zone, retaining the instant.
     */
    public function withTimeZoneSameInstant(TimeZone $timeZone): self;

    /**
     * Returns a copy of this date-time with the time-zone set to the offset.
     *
     * This returns a zoned date-time where the time-zone is the same as `getOffset()`.
     * The local date-time, offset and instant of the result will be the same as in this date-time.
     *
     * Setting the date-time to a fixed single offset means that any future
     * calculations, such as addition or subtraction, have no complex edge cases
     * due to time-zone rules.
     * This might also be useful when sending a zoned date-time across a network,
     * as most protocols, such as ISO-8601, only handle offsets, and not region-based time zones.
     */
    public function withFixedOffsetTimeZone(): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified Period added.
     */
    public function plusPeriod(Period $period): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified Duration added.
     */
    public function plusDuration(Duration $duration): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in years added.
     */
    public function plusYears(int $years): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in months added.
     */
    public function plusMonths(int $months): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in weeks added.
     */
    public function plusWeeks(int $weeks): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in days added.
     */
    public function plusDays(int $days): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in hours added.
     */
    public function plusHours(int $hours): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in minutes added.
     */
    public function plusMinutes(int $minutes): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in seconds added.
     */
    public function plusSeconds(int $seconds): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified Period subtracted.
     */
    public function minusPeriod(Period $period): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified Duration subtracted.
     */
    public function minusDuration(Duration $duration): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in years subtracted.
     */
    public function minusYears(int $years): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in months subtracted.
     */
    public function minusMonths(int $months): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in weeks subtracted.
     */
    public function minusWeeks(int $weeks): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in days subtracted.
     */
    public function minusDays(int $days): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in hours subtracted.
     */
    public function minusHours(int $hours): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in minutes subtracted.
     */
    public function minusMinutes(int $minutes): self;

    /**
     * Returns a copy of this ZonedDateTimeInterface with the specified period in seconds subtracted.
     */
    public function minusSeconds(int $seconds): self;

    /**
     * Compares this ZonedDateTimeInterface with another.
     *
     * The comparison is performed on the instant.
     *
     * @return int [-1,0,1] If this zoned date-time is before, on, or after the given one.
     */
    public function compareTo(ZonedDateTimeInterface $that): int;

    /**
     * Returns whether this ZonedDateTimeInterface equals another.
     *
     * The comparison is performed on the instant.
     */
    public function isEqualTo(ZonedDateTimeInterface $that): bool;

    /**
     * Returns whether this ZonedDateTimeInterface is after another.
     *
     * The comparison is performed on the instant.
     */
    public function isAfter(ZonedDateTimeInterface $that): bool;

    /**
     * Returns whether this ZonedDateTimeInterface is after or equal to another.
     *
     * The comparison is performed on the instant.
     */
    public function isAfterOrEqualTo(ZonedDateTimeInterface $that): bool;

    /**
     * Returns whether this ZonedDateTimeInterface is before another.
     *
     * The comparison is performed on the instant.
     */
    public function isBefore(ZonedDateTimeInterface $that): bool;

    /**
     * Returns whether this ZonedDateTimeInterface is before or equal to another.
     *
     * The comparison is performed on the instant.
     */
    public function isBeforeOrEqualTo(ZonedDateTimeInterface $that): bool;

    public function isBetweenInclusive(ZonedDateTimeInterface $from, ZonedDateTimeInterface $to): bool;

    public function isBetweenExclusive(ZonedDateTimeInterface $from, ZonedDateTimeInterface $to): bool;

    /**
     * Returns whether this ZonedDateTimeInterface is in the future, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public function isFuture(?Clock $clock = null): bool;

    /**
     * Returns whether this ZonedDateTimeInterface is in the past, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public function isPast(?Clock $clock = null): bool;

    /**
     * Converts this ZonedDateTimeInterface to a native DateTime object.
     *
     * Note that the native DateTime object supports a precision up to the microsecond,
     * so the nanoseconds are rounded down to the nearest microsecond.
     */
    public function toDateTime(): \DateTime;

    public function toDateTimeImmutable(): \DateTimeImmutable;

    /**
     * Serializes as a string using {@see ZonedDateTime::__toString()}.
     */
    public function jsonSerialize(): string;

    public function __toString(): string;
}