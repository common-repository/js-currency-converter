<?php
/**
 * @package JS Currency Converter
 * @version 1.1
 *
 * Required by js_currency_converter-init.php
 * This document contains all the front-end functions for the dragonet page like functionality
 */


if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('You are not allowed to call this page directly.');
}


class JsCurrencyConverter
{
    /**
     * @var string
     */
    protected $_version = '1.1';

    /**
     * @var string
     */
    protected $_slug = 'js_currency_converter';

    /**
     * @var string
     */
    protected $_default_currency = 'USD';

    /**
     * @var string
     */
    protected $_flags_dir = 'assets/flags';

    /**
     * Initial actions
     */
    public function setActions()
    {

        add_action('plugins_loaded', [$this, 'initTextdomain']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);

        add_filter('add_currency_converter_dropdown', [$this, 'addCurrencyConverterDropdown']);

        add_shortcode('currency_converter', [$this, 'shortcode']);

        if (get_option('jcc_exchange_rates_from')) {
            $this->_default_currency = esc_attr(get_option('jcc_exchange_rates_from'));
        }
    }

    /**
     * Init the translations
     */
    public function initTextdomain()
    {
        $plugin_dir = basename(dirname(__FILE__));
        load_plugin_textdomain($this->_slug);
    }

    /**
     * Enqueue the scripts for the ajax calls
     */
    public function enqueueScripts()
    {
        /*
         * Load JavaScript
         */
        wp_register_script('JsCurrencyConverterSelect2',
            plugin_dir_url(__FILE__).'assets/js/select2.min.js',
            ['jquery'],
            '4.0.3',
            true);

        wp_register_script('currency',
            plugin_dir_url(__FILE__).'assets/js/currency.min.js',
            ['jquery'],
            '0.2',
            true);

        wp_register_script('JsCurrencyConverter',
            plugin_dir_url(__FILE__).'assets/js/js_currency_converter.js',
            ['jquery', 'currency', 'JsCurrencyConverterSelect2'],
            $this->_version,
            true);
        /*
         * Load Stylesheets
         */
        $i18n = [
            'ajaxUrl'         => admin_url('admin-ajax.php'),
            'ajaxNonce'       => wp_create_nonce($this->_slug),
            'from'            => esc_attr(get_option('jcc_exchange_rates_from')),
            'target'          => esc_html(get_option('jcc_target_class')),
            'exchange_rates'  => $this->getExchangeRatesJson(),
            'exchange_values' => $this->getExchangeData(),
        ];
        wp_localize_script('JsCurrencyConverter', $this->_slug, $i18n);
        wp_enqueue_script('JsCurrencyConverter');

        wp_register_style('JsCurrencyConverterSelect2Css',
            plugin_dir_url(__FILE__).'assets/css/select2.min.css',
            null,
            $this->_version,
            'all');

        wp_register_style('JsCurrencyConverter-styles',
            plugin_dir_url(__FILE__).'assets/css/js_currency_converter.css',
            ['JsCurrencyConverterSelect2Css'],
            $this->_version,
            'all');
        wp_enqueue_style('JsCurrencyConverter-styles');

    }

    /**
     * Retrieve the live exchange rates
     *
     * @return array
     */
    public function getExchangeRatesJson()
    {

        $from           = esc_attr(get_option('jcc_exchange_rates_from'));
        $exchange_rates = [];
        $currency_list  = preg_split("/[\r\n]/", get_option('jcc_exchange_rates', true));
        foreach ($currency_list as $currency) {
            if (false === strpos($currency, ':')) {
                continue;
            }
            $rate                   = explode(':', $currency);
            $title                  = preg_replace('/^'.$from.'/', '', $rate[0]);
            $exchange_rates[$title] = $rate[1];
        }

        return $exchange_rates;
    }

    /**
     * @return array
     */
    private function getExchangeData()
    {
        $currencies = [];
        foreach (get_option('jcc_currency') as $currency) {
            if (! isset($currency['title'])) {
                continue;
            }
            $currencies[$currency['title']] = $currency;
        }

        return $currencies;
    }

    /**
     * Generate the curency converter
     *
     * @return string
     */
    public function addCurrencyConverterDropdown()
    {
        echo $this->shortcode();
    }

    /**
     * @return string
     */
    public function shortcode()
    {
        $currencies_list = $this->get_currency_names(get_option('jcc_currency'));
        $flag_url        = plugin_dir_url(__FILE__).$this->_flags_dir.'/';

        $html = '<div class="js_currency_converter">'.
                '<select class="js_currency_converter_select" name="js_currency_converter">';
        foreach ($currencies_list as $currency_name => $currency) {
            $html .= '  <option name="'.$currency_name.'" data-image="'.$flag_url.$currency['flag'].'"';
            if ($this->_default_currency == $currency_name) {
                $html .= ' selected="selected"';
            };
            $html .= '>'.$currency_name.'</option>';
        }
        $html .= '</select></div>';

        return $html;
    }

    /**
     * Get a list with the currency names
     *
     * @param $currencies
     *
     * @return array|string
     */
    private function get_currency_names($currencies)
    {
        if (! is_array($currencies) || empty($currencies)) {
            return '';
        }
        $currencies_names = [];
        /*
         * Walk trough all the stored currencies
         */
        foreach ($currencies as $currency) {
            if (! isset($currency['title']) || empty($currency['title'])) {
                continue;
            }
            $currencies_names[$currency['title']] = $currency;
        }

        return $currencies_names;
    }
}