<?php

/**
 * Class get
 */
final class get {

    const HASH_SALT = 'aeauG%4xq$jTv&a_pEWQYmw^?ySw@vN*C@HVh^3gaeNZ_e&*5X';

    /**
     * @param array $data
     * @param int   $history_periods
     *
     * @return bool|string
     */
    public static function historicalPriceDirection(array $data, $history_periods = 5) {
        $data = array_slice($data, -$history_periods);

        if (count($data) === $history_periods) {
            /**@var avg_price_data $start*/
            $start = array_shift($data);
            $last_price = $start->close;
            $difference = 0;

            foreach ($data as $row) {
                /**@var avg_price_data $row*/
                $difference += self::pipDifference($last_price, $row->close, $row->pair, false);
                $last_price = $row->close;
            }

            if ($difference > 0) {
                return 'up';
            } else if ($difference < 0) {
                return 'down';
            } else {
                return 'neutral';
            }
        }

        return false;
    }

    /**
     * @param string $uk_date_string
     *
     * @return string
     */
    public static function strtotime_from_uk_format(string $uk_date_string): string {
        list($day, $month, $year) = explode('/', $uk_date_string);

        return strtotime($month . '/' . $day . '/' . $year);
    }

    /**
     * @param string $date_string
     * @param string $uk_date_string
     *
     * @return string
     */
    public static function date(string $date_string, string $uk_date_string): string {
        return date($date_string, self::strtotime_from_uk_format($uk_date_string));
    }

    /**
     * @param array $full_data
     * @param int   $average_period
     *
     * @return float
     */
    public static function averageCandleSize(array $full_data, int $average_period = 15): float {
        $period_data = array_slice($full_data, -$average_period);

        $sum = 0;
        foreach ($period_data as $candle) {
            /**@var avg_price_data $candle */
            $candle_size = abs($candle->high - $candle->low);

            $sum += $candle_size;
        }

        return ($sum / count($period_data));
    }

    /**
     * @param float  $a
     * @param float  $b
     * @param \_pair $pair
     * @param bool   $abs
     *
     * @return float
     */
    public static function pipDifference(float $a, float $b, _pair $pair, $abs = true): float {
        $multiplier = 10000; // For currency pairs displayed to four decimal places, one pip is equal to 0.0001
        if ($pair->base_currency === 'JPY' || $pair->quote_currency === 'JPY') {
            // Yen-based currency pairs are an exception and are displayed to only two decimal places (0.01)
            $multiplier = 100;
        }

        if ($abs) {
            return (abs($a - $b) * $multiplier);
        } else {
            return (($a - $b) * $multiplier);
        }
    }


    /**
     * @param string $string
     *
     * @return int|float|string
     */
    public static function int_float_from_string($string) {
        if (is_numeric($string)) {
            $float_val = floatval($string);
            if ($float_val == (int) $float_val) {
                return (int) $float_val;
            } else {
                return (float) $float_val;
            }
        }

        return $string;
    }

    /**
     * @param string $data
     *
     * @return string
     */
    public static function hash(string $data): string {
        return hash_hmac('sha512', $data, self::HASH_SALT);
    }

    /**
     * @return string
     */
    public static function ip() {
        if (defined('ip')) {
            return ip;
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Request has been through a proxy (or two...)
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        return $_SERVER['REMOTE_ADDR'];
    }


    /**
     * @param array  $list
     * @param string $final_concatenation_word
     *
     * @return string
     */
    public static function array_to_sentence_list(array $list, string $final_concatenation_word = 'and'): string {
        if (count($list) > 1) {
            $last_element = array_pop($list);

            return implode(', ', $list) . ' ' . $final_concatenation_word . ' ' . $last_element;
        }

        return implode(', ', $list);
    }

    /**
     * @param array $attrs
     *
     * @return string
     */
    public static function attrs(array $attrs): string {
        $return = '';
        foreach ($attrs as $attr => $values) {
            $value = (is_array($values) ? implode(' ', $values) : $values);
            if (!empty($value) && !empty($attr)) {
                $return .= ' ' . $attr . '="' . htmlentities($value) . '"';
            }
        }

        return $return;
    }
}