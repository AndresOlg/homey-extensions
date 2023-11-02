<?php

namespace UserHX\Models;

class UserProfileData
{
    public static $table_name;

    public function __construct()
    {
        static::$table_name = HX_PREFIX . 'user_profile_data';
    }

    public function add($data)
    {
        global $wpdb;
        return $wpdb->insert(static::$table_name, $data);
    }

    public static function get($user_id)
    {
        global $wpdb;
        $table_name = static::$table_name;
        return $wpdb->get_row("SELECT * FROM $table_name WHERE user_id = $user_id", ARRAY_A);
    }

    public static function getAll()
    {
        global $wpdb;
        $table_name = static::$table_name;
        return $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    }

    public static function getByAttr($attr, $value)
    {
        global $wpdb;
        $table_name = $table_name = static::$table_name;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE $attr = %s", $value), ARRAY_A);
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
            $table_name = $table_name = static::$table_name;
            $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE $where_clause");
            return $wpdb->get_results($sql, ARRAY_A);
        } else {
            return array();
        }
    }

    public function update($id, $data)
    {
        global $wpdb;
        return $wpdb->update($this->table_name, $data, array('id' => $id));
    }

    public function delete($id)
    {
        global $wpdb;
        return $wpdb->delete($this->table_name, array('id' => $id));
    }
}
