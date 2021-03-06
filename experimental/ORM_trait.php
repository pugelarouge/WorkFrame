<?php

/**
 * This was an experiment... In real life if you really need ORM, use one of 
 * the highly evolved solutions out there like doctrine or propel
 */

namespace WorkFrame;

trait ORM_trait {
	function find_by_primary_key($primary_key_value) {
		$this->_check_ORM_config(__METHOD__);
		$data = $this->repository->generic_select_where(self::$_orm_table, self::$_orm_select_fields, [self::$_orm_primary_key_field => $primary_key_value]);
		if($data) {
			$this->populate_from_array($data, FALSE);
			return TRUE;
		}
		return FALSE;
	}
	function save() {
		$this->_check_ORM_config(__METHOD__);
		$primary_key_field = self::$_orm_primary_keys_field;
		if(empty($this->$primary_key_field)) {
			$key_values = $this->_get_field_values(self::$_orm_insert_fields);
			$this->$primary_key_field = $this->repository->generic_insert(self::$_orm_table, $key_values);
		} else {
			$key_values = $this->_get_field_values(self::$_orm_update_fields);
			$this->repository->generic_update(self::$_orm_table, $key_values, [self::$_orm_primary_key_field, $this->$primary_key_field]);
		}
		return $this->$primary_key_field;
	}
	function delete() {
		return $this->repository->generic_delete(self::$_orm_table, [self::$_orm_primary_key_field, $this->$primary_key_field]);
	}
	// TODO!!
	function with($relation) {
	}


	private function _get_field_values($fields) {
		$data = [];
		foreach($fields as $field) {
			$get_func_name = 'get_'.$field;
			if(method_exists($this, $get_func_name)) {
				$value = $this->$get_func_name();
			} else  {
				$value = $this->$field;
			}
			$data[$field] = $value;
		}
		return $data;
	}

	private function _check_ORM_config($function_name) {
		if(is_null($this->repository)) {
			throw new Unset_repository_exception('This function '.$function_name.' of '.__CLASS__.' requires a repository');
		}
		if(!isset(self::$_orm_table, self::$_orm_primary_key_field)) {
			throw new Unset_ORM_info_exception('Make sure you define all ORM config fields ($_orm_table, $_orm_primary_key_field, $_orm_insert_fields, $_orm_update_fields, $_orm_select_fields)');
		}
	}
}
