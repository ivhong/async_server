<?php
namespace models;
use library\Common;

abstract class BaseModel{
    public static function tablename(){
        
    }
    
    public static function getOne($id){
        $db = Common::getDB();
        $sql = "SELECT * FROM ".static::tablename()." WHERE `id`=".intval($id);
        $res = $db->query($sql);
        if( !empty( $res ) ){
            return $res[0];
        }
        return [];
    }
    
    public static function saveData($data){
        $db = Common::getDB();
        if( isset($data['id']) ){
            $id = $data['id'];
            unset($data['id']);
            $values = [];
            foreach($data as $f => $v){
                $values[] = '`'.$f.'`=\''.  addslashes($v).'\'';
            }
            $sql = "UPDATE ".static::tablename()." SET ".implode(', ', $values) . " WHERE id=".intval($id);
            $db->query($sql);
        }else{
            $fields = [];
            $values = [];
            foreach($data as $f => $v){
                $fields[] = '`'.$f.'`';
                $values[] = '\''.  addslashes($v).'\'';
            }
            
            $sql = "INSERT INTO ".static::tablename()." (".implode(',', $fields).") VALUES(".implode(',', $values).")";
            $db->query($sql);
            
            $id = $db->insertid();
        }
        
        return $id;
    }
    
    public static function getList($where=''){
        $db = Common::getDB();
        $sql = "SELECT * FROM ".static::tablename();
        if($where){
            $sql .= " WHERE " . $where;
        }
        $res = $db->query($sql);
        return $res;
    }
    
    public static function query($sql){
        $db = Common::getDB();
        $res = $db->query($sql);
        return $res;
    }
}