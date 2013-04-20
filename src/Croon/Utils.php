<?php

namespace Croon;

class Utils
{
    /**
     * Parse line
     *
     * @param $line
     * @return array|bool
     */
    public static function parseLine($line)
    {
        $line = trim($line);

        // ignore blank line and comment line
        if ($line == '' || $line{0} == '#') return false;

        // check format
        if (!preg_match('/^(((\*|\d+|\d+\-\d+)(\/\d+)? |((\*|\d+|\d+\-\d+)(\/\d+)?,)+(\*|\d+|\d+\-\d+)(\/\d+)? ){5,6})(.*)$/', $line, $match)) return false;

        // Make compatible with system crontab
        if (substr_count(trim($match[1]), ' ') === 4) {
            $match[1] = '0 ' . $match[1];
        }

        return array($match[1], $match[10]);
    }

    /**
     * Check rule
     *
     * @static
     * @param      $rule
     * @return bool
     */
    public static function checkRule($rule)
    {
        // Get command and cycles
        $chunks = explode(' ', trim($rule));

        // Process
        foreach ($chunks as $index => $chunk) {
            // Explode chunks
            $slices = explode(',', $chunk);
            $hit = false;

            // Process check index
            foreach ($slices as $slice) {
                if (self::checkIndexSlice($index, $slice)) {
                    $hit = true;
                    break;
                }
            }

            // If one index not hit, return false
            if (!$hit) return false;
        }

        return true;
    }

    /**
     * Check chunk of index split
     *
     * @static
     * @param $index
     * @param $slice
     * @return bool
     */
    public static function checkIndexSlice($index, $slice)
    {
        // Current time
        $cur_time = self::getCurTime();
        // Current index time
        $index_time = self::getCurIndexTime($index);
        // Start time
        $start_time = self::getStartTime();

        // pre chunk
        $is_valid = false;

        $pre = $sub = '';
        // Extract pre and sub
        if (strpos($slice, '/')) list($pre, $sub) = explode('/', $slice);
        else $pre = $slice;

        // if pre is *, pre is ok.
        if ($pre == '*') {
            $is_valid = true;
        } // if pre include "-" then star range mode.
        elseif (strpos($pre, '-') !== false) {
            list($left, $right) = explode('-', $pre);

            // left, right must be under rules.
            if (!self::checkIndexRange($index, $left) || !self::checkIndexRange($index, $right)) return false;

            // Check range
            if ($left < $right) {
                if ($index_time >= $left && $index_time <= $right) $is_valid = true;
            } else {
                if ($index_time >= $left || $index_time <= $right) $is_valid = true;
            }
        } elseif (is_numeric($pre)) {
            // Check range
            if (!self::checkIndexRange($index, $pre)) return false;

            // If time on then pre is ok.
            if ($pre == $index_time) $is_valid = true;
        } else {
            return false;
        }

        // If pre is invalid or not sub.
        if (!$is_valid || !$sub) return $is_valid;

        // Check sub range
        if (!self::checkIndexRange($index, $sub)) return false;

        // To number
        $sub = (int)$sub;

        // Check every cycle
        switch ($index) {
            case 0:
                // Second check.
                if (($cur_time - $start_time) % $sub == 0) return true;
                break;
            case 1:
                // Minutes check
                if (floor(($cur_time - $start_time) / 60) % $sub == 0) return true;
                break;
            case 2:
                // Hour check
                if (floor(($cur_time - $start_time) / 3600) % $sub == 0) return true;
                break;
            case 3:
                // Day check
                if (floor(($cur_time - $start_time) / 86400) % $sub == 0) return true;
                break;
            case 4:
                // Month check
                $date1 = explode('-', date('Y-m', $start_time));
                $date2 = explode('-', date('Y-m', $cur_time));
                $month = abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]);
                if ($month & $sub == 0) return true;
                break;
            case 5:
                // Week check
                if (floor(($cur_time - $start_time) / 86400 / 7) % $sub == 0) return true;
                break;
        }
        return false;
    }

    /**
     * Get start time
     *
     * @static
     * @return int
     */
    public static function getStartTime()
    {
        static $start_time = null;
        if ($start_time === null) $start_time = strtotime(date('Y-01-01 00:00:00'));
        return $start_time;
    }

    /**
     * Get current index time
     *
     * @static
     * @param      $index
     * @param null $time
     * @return string
     */
    public static function getCurIndexTime($index, $time = null)
    {
        static $index_map = array('s', 'i', 'H', 'd', 'm', 'w');
        return date($index_map[$index], $time ? $time : time());
    }

    /**
     * Get current time
     *
     * @static
     * @return int
     */
    public static function getCurTime()
    {
        return time();
    }

    /**
     * Check Range
     *
     * @param $index
     * @param $time
     * @return bool
     */
    public static function checkIndexRange($index, $time)
    {
        switch ($index) {
            case 0:
                if ($time >= 0 && $time < 60) {
                    return true;
                }
                break;
            case 1:
                if ($time >= 0 && $time < 60) {
                    return true;
                }
                break;
            case 2:
                if ($time >= 0 && $time < 24) {
                    return true;
                }
                break;
            case 3:
                if ($time > 0 && $time <= 31) {
                    return true;
                }
                break;
            case 4:
                if ($time > 0 && $time <= 12) {
                    return true;
                }
                break;
            case 5:
                if ($time > 0 && $time <= 7) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     *
     * Exec the command and return code
     *
     * @param string $cmd
     * @param string $stdout
     * @param string $stderr
     * @param int    $timeout
     * @return int|null
     */
    public static function exec($cmd, &$stdout, &$stderr, $timeout = 3600)
    {
        if ($timeout <= 0) $timeout = 3600;
        $descriptors = array
        (
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $stdout = $stderr = $status = null;
        $process = proc_open($cmd, $descriptors, $pipes);

        $time_end = time() + $timeout;
        if (is_resource($process)) {
            do {
                $time_left = $time_end - time();
                $read = array($pipes[1]);
                stream_select($read, $null, $null, $time_left, NULL);
                $stdout .= fread($pipes[1], 2048);
            } while (!feof($pipes[1]) && $time_left > 0);
            fclose($pipes[1]);

            if ($time_left <= 0) {
                proc_terminate($process);
                $stderr = 'process terminated for timeout.';
                return -1;
            }

            while (!feof($pipes[2])) {
                $stderr .= fread($pipes[2], 2048);
            }
            fclose($pipes[2]);

            $status = proc_close($process);
        }

        return $status;
    }

    /**
     * Convert to human readable unit
     *
     * @param int $size
     * @return string
     */
    public static function convertUnit($size)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}
