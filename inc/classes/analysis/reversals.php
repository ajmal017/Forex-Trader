<?php

/**
 * Class reversals
 */
class reversals extends _base_analysis {

    const REQUIRED_CONFLUENCE = 2;
    const HISTORICAL_PRICE_DIRECTION_PERIODS = 4; // The number of days to check historical price movement against

    /**
     * @var int
     */
    protected $data_fetch_size = 250;

    /**
     * @var bool
     */
    protected $enabled = true;

    private $signals = [
        'long' => [
            'low_test',
            'doji',
            'inside_bar',
            'tweezer_bottoms',
        ],
        'short' => [
            'high_test',
            'doji',
            'inside_bar',
            'tweezer_tops',
        ]
    ];

    /**
     * @param \_pair $currency_pair
     */
    public function setPair(_pair $currency_pair) {
        parent::setPair($currency_pair);

        $this->currency_pair->data_fetch_time = '1d';
    }

    /**
     * @return bool|string
     */
    public function getHistoricalPriceDirection() {
        return get::historicalPriceDirection($this->getData(), self::HISTORICAL_PRICE_DIRECTION_PERIODS);
    }

    /**
     * @return bool
     */
    protected function isLongEntry(): bool {
        if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {

            $choppiness = $this->getChoppinessIndex();

            if ($choppiness <= 60 && $choppiness >= 20) {
                $data = $this->getData();

                if ($this->getHistoricalPriceDirection() === 'down') { // Price has moved down over the last few days
                    $confluence_factors = 0;
                    foreach ($this->signals['long'] as $signal) {
                        /**@var _signal $signal */
                        if ($signal::isValidSignal($data, 'long')) { // TODO; Long - Tweezer Bottoms & Inside Bar
                            $confluence_factors++;

                            /*$latest_day = end($data);
                            echo '<p style="font-weight:bold;color:red;">Long confluence from: ' . ucwords(str_replace('_', ' ', $signal)) . ' on ' . $latest_day->pair->getPairName() . '</p>'."\n";
                            echo '<p><pre>' . print_r($latest_day, true) . '</pre></p>';*/
                        }
                    }

                    if ($confluence_factors >= self::REQUIRED_CONFLUENCE) {
                        //echo '<p>' . $data[0]->pair->getPairName() . ' - Confluence: ' . $confluence_factors . '</p>' . "\n";

                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isShortEntry() {
        $this->getHistoricalPriceDirection();
        if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {

            $choppiness = $this->getChoppinessIndex();
            if ($choppiness <= 60 && $choppiness >= 20) {

                $data = $this->getData();

                if ($this->getHistoricalPriceDirection() === 'up') { // Price has moved up over the last few days
                    $confluence_factors = 0;
                    foreach ($this->signals['short'] as $signal) {
                        /**@var _signal $signal */
                        if ($signal::isValidSignal($data, 'short')) { // TODO; Short - Tweezer Tops & Inside Bar
                            $confluence_factors++;

                            /*$latest_day = end($data);
                            echo '<p style="font-weight:bold;color:red;">Short confluence from: ' . ucwords(str_replace('_', ' ', $signal)) . ' on ' . $latest_day->pair->getPairName() . '</p>' . "\n";
                            echo '<p><pre>' . print_r($latest_day, true) . '</pre></p>';*/
                        }
                    }

                    if ($confluence_factors >= self::REQUIRED_CONFLUENCE) {
                        //echo '<p>' . $data[0]->pair->getPairName() . ' - Confluence: ' . $confluence_factors . '</p>' . "\n";

                        return true;
                    }
                }
            }
        }

        return false;
    }
}