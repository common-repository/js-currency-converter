<?php
/**
 * @package JS Currency Converter
 * @version 1.1
 *
 * Required by js_currency_converter-init.php
 * This document contains all the admin functions for the JS Currency Converter.
 */


if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('You are not allowed to call this page directly.');
}


class JsCurrencyConverterAdmin
{

    /**
     * @var string
     */
    protected $_version = '1.2';

    /**
     * @var string
     */
    protected $_slug = 'js_currency_converter';

    /**
     * @var string
     */
    protected $_flags_dir = 'assets/flags';

    /**
     * @var array
     */
    protected $_from_currency = ['USD', 'EUR'];

    /**
     * AdminSettings constructor.
     */
    function __construct()
    {

        /*
         * initialize admin settings when needed
         */
        if (! is_admin()) {
            return false;
        }

        /*
         * Initialize the settings
         */
        add_action('admin_init', [$this, 'action__jcc_RegisterSettings']);
        add_action('admin_menu', [$this, 'action__jcc_CreateMenu']);

        /*
         * Admin part
         */
        add_action('admin_enqueue_scripts', [$this, 'action__jcc_admin_scripts']);

        return true;
    }

    /**
     * Load the admin javascript
     *
     * @param $hook
     */
    public function action__jcc_admin_scripts()
    {

        /*
         * Load JavaScript
         */
        wp_register_script('JsCurrencyConverterSelect2',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
            ['jquery'],
            '4.0.3',
            true);

        wp_register_script('JsCurrencyConverterAdmin',
            plugin_dir_url(__FILE__).'assets/js/js_currency_converter_admin.js',
            ['jquery'],
            $this->_version,
            true);

        $i18n = [
            'ajaxUrl'            => admin_url('admin-ajax.php'),
            'ajaxNonce'          => wp_create_nonce($this->_slug),
            'target'             => esc_html(get_option('jcc_target_class')),
            'exchange_rates_api' => esc_html(get_option('jcc_exchange_rates_api')),
        ];

        wp_localize_script('JsCurrencyConverterAdmin', $this->_slug, $i18n);
        wp_enqueue_script('JsCurrencyConverterAdmin');
        wp_enqueue_script('JsCurrencyConverterSelect2');

        /*
         * Sload CSS
         */
        wp_register_style('JsCurrencyConverterCss',
            plugin_dir_url(__FILE__).'assets/css/js_currency_converter.css',
            null,
            $this->_version,
            'all');

        wp_register_style('JsCurrencyConverterSelect2Css',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css',
            null,
            $this->_version,
            'all');
        wp_enqueue_style('JsCurrencyConverterCss');
        wp_enqueue_style('JsCurrencyConverterSelect2Css');
    }

    /**
     * Create the menu item in the settings part
     */
    public function action__jcc_CreateMenu()
    {
        add_submenu_page('options-general.php',
            ucfirst($this->_slug),
            'JS Currency Converter',
            'administrator',
            __FILE__,
            [$this, 'jcc_options_page']);
    }

    /**
     * The settings for this site than can be updated by the customer
     */
    public function action__jcc_RegisterSettings()
    {
        register_setting($this->_slug.'_settings', 'jcc_target_class');
        register_setting($this->_slug.'_settings', 'jcc_currency');
        register_setting($this->_slug.'_settings', 'jcc_exchange_rates');
        register_setting($this->_slug.'_settings', 'jcc_exchange_rates_from');
        register_setting($this->_slug.'_settings', 'jcc_exchange_rates_api');
    }

    /**
     * The settings page in the admin menu
     */
    public function jcc_options_page()
    {
        echo '<div class="wrap">';
        echo '	<h2>'.__('JS Currency Converter settings', $this->_slug).'</h2>';
        echo '	<form method="post" action="options.php">';

        settings_fields($this->_slug.'_settings');
        do_settings_sections($this->_slug.'_settings');

        echo '<div class="card" style="width:90%; max-width:90%;">';
        echo '	<table class="form-table">';

        echo '	<tr valign="top">';
        echo '		<th scope="row">'.__('The original currency', $this->_slug).'</th>';
        echo '		<td><select name="jcc_exchange_rates_from">';
        foreach ($this->_from_currency as $currency) {
            echo '  <option name="'.$currency.'"';
            if (get_option('jcc_exchange_rates_from') == $currency) {
                echo ' selected="selected"';
            };
            echo '>'.$currency.'</option>';
        }
        echo '	</select><em><br>';
        echo sprintf(__('Special subscription needed at <a href="%s" target="currency_list">%s</a>', $this->_slug),
            'https://currencylayer.com/', 'https://currencylayer.com/');
        echo '		</em></td>';
        echo '	</tr>';

        echo '	<tr valign="top">';
        echo '		<th scope="row">'.__('Complete API url<br>including the API-KEY', $this->_slug).'</th>';
        echo '		<td><input type="text" name="jcc_exchange_rates_api" value="'.esc_attr(get_option('jcc_exchange_rates_api')).'" style="width:65%;" />';
        echo '		<br><em>'.__('Example of a free exchange rate API can be found here: <a href="https://coinlayer.com" target="api_layer">https://coinlayer.com</a>',
                $this->_slug).'</em></td>';
        echo '	</tr>';

        echo '	<tr valign="top">';
        echo '		<th scope="row">'.__('HTML class of target element which contain the price',
                $this->_slug).'</th>';
        echo '		<td><input type="text" name="jcc_target_class" value="'.esc_attr(get_option('jcc_target_class')).'" style="width:65%;" /></td>';
        echo '	</tr>';

        echo '	<tr valign="top">';
        echo '		<th scope="row">'.__('Exchange rates', $this->_slug).'</th>';
        echo '		<td>';
        echo '		<div class="jcc_currency_admin_exchange_holder">'.__('My Exchange rates<br><small>These are used at the frontend</small>',
                $this->_slug).'<br><textarea name="jcc_exchange_rates" class="jcc_exchange_rates">'.esc_html(get_option('jcc_exchange_rates')).'</textarea></div>';
        echo '      <div class="jcc_currency_admin_exchange_holder">'.__('Example Exchange rates<br><small>From the live feed</small>',
                $this->_slug).'<br><div class="currency_holder">'.$this->retrieve_exchange_rates().'</div></div></td>';
        echo '	</tr>';

        echo '	<tr valign="top">';
        echo '		<th scope="row">'.__('Currencies', $this->_slug).'</th>';
        echo '		<td>';
        $this->create_currency_list();
        echo '<br/><em>';
        echo __('Every currency should be on a new line', $this->_slug).'<br>';
        echo __('A available currency list can be retrieved from your API provider', $this->_slug);
        echo '		</em></td>';
        echo '	</tr>';

        echo '	<tr valign="top">';
        echo '		<th scope="row"></th>';
        echo '		<td>';
        echo get_submit_button();
        echo '		</td>';
        echo '	</tr>';
        echo '	</table>';
        echo '</div>';


        echo '</form></div>';
    }

    /**
     * Retrieve the live exchange rates
     *
     * @return string
     */
    private function retrieve_exchange_rates()
    {
        $api_url    = esc_attr(get_option('jcc_exchange_rates_api'));
        $from       = esc_attr(get_option('jcc_exchange_rates_from'));
        $currencies = $this->get_currency_names(get_option('jcc_currency'));
        $currencies = join(',', $currencies);
        $url        = $api_url.'&currencies='.$currencies;
        if ('USD' != $from) {
            $url .= '&source='.$from;
        }

        $response = wp_remote_get($url);
        $body     = wp_remote_retrieve_body($response);

        $exchangeRates = json_decode($body, true);
        if (! isset($exchangeRates['quotes'])) {
            $error = '';
            if (! empty($exchangeRates['error']['info'])) {
                $error = $exchangeRates['error']['info'];
            }

            return sprintf(
                __('API(key) is not valid : %s<br>%s', $this->_slug),
                $url,
                $error);
        }

        $output = '';
        foreach ($exchangeRates['quotes'] as $type => $rate) {
            $output .= $type.':'.$rate."\n";
        }

        return nl2br($output);
    }

    /**
     * Generate text field
     *
     * @param Array
     *
     * @return String
     */
    public function create_currency_list()
    {
        $base_name  = 'jcc_currency';
        $currencies = get_option('jcc_currency');

        echo '<div class="jcc_currency_admin_currency_rows_titles">';
        echo '<span>Flag</span>';
        echo '<span class="jcc_currency_admin_title">Currency</span>';
        echo '<span class="jcc_currency_admin_symbol">Symbol</span>';
        echo '<span class="jcc_currency_admin_decimal">Decimal</span>';
        echo '<span class="jcc_currency_admin_separator">Separator</span>';
        echo '<span>&nbsp;</span>';
        echo '</div>';
        echo '<div class="currency_rows">';
        $i = $this->get_currency_list($base_name, $currencies);
        echo '</div>';
        echo '<div class="new_row currency_row" style="display:none;" data-basename="'.$base_name.'">';
        echo $this->get_flag_list($base_name.'['.$i.'][flag]', '', true);
        echo '  <input placeholder="Title" class="jcc_currency_admin_title" type="text" name="'.$base_name.'['.$i.'][ title ]" />';
        echo '	<input placeholder="Symbol" class="jcc_currency_admin_symbol" type="text" name="'.$base_name.'['.$i.'][symbol]" value="$" />';
        echo '	<input placeholder="Decimal" class="jcc_currency_admin_decimal" type="text" name="'.$base_name.'['.$i.'][decimal]" value="," />';
        echo '	<input placeholder="Separator" class="jcc_currency_admin_separator" type="text" name="'.$base_name.'['.$i.'][separator]" value="." />';
        echo '</div>';
        echo '<button class="button new_row_button">'.__('New Currency', $this->_slug).'</button>';
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
        foreach ($currencies as $currency) {
            if (! isset($currency['title']) || empty($currency['title'])) {
                continue;
            }
            $currencies_names[] = $currency['title'];
        }

        return $currencies_names;
    }

    /**
     * Create a list with all the currencies
     *
     * @param $base_name
     * @param $currencies
     *
     * @return int
     */
    private function get_currency_list($base_name, $currencies)
    {
        if (! is_array($currencies) || empty($currencies)) {
            return 0;
        }

        $i = 0;
        foreach ($currencies as $currency) {
            if (! isset($currency['title']) || empty($currency['title'])) {
                continue;
            }
            $symbol    = (! empty($currency['symbol'])) ? $currency['symbol'] : '';
            $decimal   = (! empty($currency['decimal'])) ? $currency['decimal'] : ',';
            $separator = (! empty($currency['separator'])) ? $currency['separator'] : '.';

            echo '<div class="currency_row line" data-basename="'.$base_name.'">';
            echo $this->get_flag_list($base_name.'['.$i.'][flag]', $currency['flag']);
            echo '	<input placeholder="Title" class="jcc_currency_admin_title" type="text" name="'.$base_name.'['.$i.'][title]" value="'.$currency['title'].'" />';
            echo '	<input placeholder="Symbol" class="jcc_currency_admin_symbol" type="text" name="'.$base_name.'['.$i.'][symbol]" value="'.$symbol.'" />';
            echo '	<input placeholder="Decimal" class="jcc_currency_admin_decimal" type="text" name="'.$base_name.'['.$i.'][decimal]" value="'.$decimal.'" />';
            echo '	<input placeholder="Separator" class="jcc_currency_admin_separator" type="text" name="'.$base_name.'['.$i.'][separator]" value="'.$separator.'" />';
            echo '	<button class="button fa-warning remove_this_row">'.__('Delete Currency',
                    $this->_slug).'</button>';
            echo '</div>';
            $i++;
        }

        return $i;
    }

    /**
     * Create a dropdown with the flags
     *
     * @param $field_name
     * @param $value
     *
     * @return string
     */
    private function get_flag_list($field_name, $value = '', $raw = false)
    {
        $flag_path   = plugin_dir_path(__FILE__).$this->_flags_dir;
        $flag_url    = plugin_dir_url(__FILE__).$this->_flags_dir.'/';
        $flags_array = [];
        $class       = '';

        /*
         * Open the dir
         */
        if (! is_dir($flag_path) || ! $dh = opendir($flag_path)) {
            return $flag_path;
        }

        /*
         * Loop trough the flags folder
         */
        while (($file = readdir($dh)) !== false) {

            if ('.png' != substr($file, -4, 4)) {
                continue;
            }

            $name               = substr($file, 0, -4);
            $flags_array[$file] = $name;
        }

        closedir($dh);
        //asort( $flags_array );

        /*
         * Create the flag dropdown
         */
        if (! $raw) {
            $class = 'jcc_currency_image_menu';
        }
        $output = '<select class="jcc_currency_admin_flag '.$class.'" name="'.$field_name.'" value="'.$value.'" />';
        foreach ($flags_array as $flag => $name) {
            $output .= '<option value="'.$flag.'" data-image="'.$flag_url.$flag.'"';
            if ($flag == $value) {
                $output .= ' selected="selected"';
            }
            $output .= '>'.$name.' </option > ';
        }
        $output .= '</select > ';

        return $output;
    }
}