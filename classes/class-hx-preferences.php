<?php
class PreferencesData
{
    private static $table_name;

    public function __construct()
    {
        self::$table_name = HX_PREFIX . 'preferences';
    }

    public function add($data)
    {
        global $wpdb;
        return $wpdb->insert(self::$table_name, $data);
    }

    public static function get($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::$table_name . " WHERE id = %d", $id), ARRAY_A);
    }

    public static function getAll()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM " . self::$table_name, ARRAY_A);
    }

    public static function getByAttr($attr, $value)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . self::$table_name . " WHERE $attr = %s", $value), ARRAY_A);
    }

    public static function getByFilters($filters)
    {
        global $wpdb;

        $where = array();

        foreach ($filters as $filter) {
            if (isset($filter['attr']) && isset($filter['value'])) {
                $where[] = $wpdb->prepare("{$filter['attr']} = %s", $filter['value']);
            }
        }

        if (!empty($where)) {
            $where_clause = implode(' AND ', $where);
            $sql = $wpdb->prepare("SELECT * FROM " . self::$table_name . " WHERE $where_clause");
            return $wpdb->get_results($sql, ARRAY_A);
        } else {
            return array();
        }
    }

    public function update($id, $data)
    {
        global $wpdb;
        return $wpdb->update(self::$table_name, $data, array('id' => $id));
    }

    public function delete($id)
    {
        global $wpdb;
        return $wpdb->delete(self::$table_name, array('id' => $id));
    }
}
