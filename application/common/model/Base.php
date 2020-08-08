<?php


namespace app\common\model;


use think\Model;

class Base extends Model
{
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 获取当前表的配置
     * @param string $name
     * [field][name][value|color|label] = String
     * [field][value][name|color|label] = String
     * [field][value_to_colors|value_to_labels|value_to_names][value] = Array
     * @return mixed|string
     */
    public function getCurrentTableFieldConfig($name = '')
    {
        $name = $this->getTable() . ($name ? '.' . $name : '');
        return self::getTableFieldConfig($name);
    }

    /**
     * 获取全部表配置
     * @param string $name
     * [table_name][field][name][value|color|label] = String
     * [table_name][field][value][name|color|label] = String
     * [table_name][field][value_to_colors|value_to_labels|value_to_names][value] = Array
     * @return array|mixed|string
     */
    public static function getTableFieldConfig($name = '')
    {
        $keys = $name ? explode('.', $name) : [];
        $output = self::parseTableFieldConfig();
        foreach ($keys as $key) {
            $output = $output[$key] ?? '';
        }
        return $output;
    }

    protected static function parseTableFieldConfig()
    {
        $tableConfig = \think\Config::get('table');
        $colorConfig = [];
        foreach ($tableConfig as $tb => $config) {
            if (isset($config['field'])) {
                foreach ($config['field'] as $field => $item) {
                    foreach ($item as $name => list($value, $color, $label)) {
                        $colorConfig[$tb][$field][$name]['value'] = $value;
                        $colorConfig[$tb][$field][$name]['color'] = $color;
                        $colorConfig[$tb][$field][$name]['label'] = $label;
                        $colorConfig[$tb][$field][$value]['name'] = $name;
                        $colorConfig[$tb][$field][$value]['color'] = $color;
                        $colorConfig[$tb][$field][$value]['label'] = $label;
                        $colorConfig[$tb][$field]['value_to_colors'][$value] = $color;
                        $colorConfig[$tb][$field]['value_to_labels'][$value] = $label;
                        $colorConfig[$tb][$field]['value_to_names'][$value] = $name;
                    }
                }
            }
        }
        return $colorConfig;
    }
}