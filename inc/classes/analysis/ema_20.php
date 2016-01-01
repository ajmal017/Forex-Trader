<?php

/**
 * Class ema_20
 */
class ema_20 extends _base_analysis {

    /**
     * @var string|null - 'major' OR 'minor'
     */
    public $signal_strength = 'major';

    /**
     * @var int
     */
    protected $data_fetch_size = 10080; // 7 days

    /**
     * @return float
     */
    function doAnalyse(): float {
        $data = $this->getData();
        $this->addEmaToData($data);
        $latest_data = end($data);

        $current_distance_from_ema = number_format(abs($latest_data['exit_price'] - $latest_data['ema_20']), 5, '.', '');

        if ($current_distance_from_ema <= 0.00002) {
            return ($current_distance_from_ema == .00002 ? .75 : 1);
        }

        return 0;
    }

    /**
     * @param array $data
     */
    protected function addEmaToData(array &$data) {
        $exit_prices = [];

        foreach ($data as $row) {
            $exit_prices[] = $row['exit_price'];
        }

        foreach (trader_ema($exit_prices, 20) as $key => $ema_20) {
            if (isset($data[$key])) {
                $data[$key]['ema_20'] = $ema_20;
            }
        }
    }
}