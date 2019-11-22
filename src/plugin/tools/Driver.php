<?php

namespace vnh_namespace\tools;

if (!defined('ABSPATH')) {
	wp_die(esc_html__('Direct access not permitted', 'vnh_textdomain'));
}

class Driver extends \PDO {
	protected $_prefix = 'wp_';

	public function setPrefix($prefix) {
		$this->_prefix = $prefix;
	}

	/**
	 * @param $table
	 * @param $where
	 * @param string $bind
	 */
	public function delete($table, $where, $bind = "") {
		$sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
		$stmt = $this->prepare($sql);
		$stmt->execute($bind);
		return $stmt->rowCount();
	}

	/**
	 * @param $table
	 * @param $info
	 * @return array
	 */
	private function filter($table, $info) {
		return array_keys($info);
	}

	/**
	 * @param $bind
	 * @return array
	 */
	private function cleanup($bind) {
		if (!is_array($bind)) {
			if (!empty($bind)) {
				$bind = array($bind);
			} else {
				$bind = array();
			}
		}
		return $bind;
	}

	/**
	 * @param $table
	 * @param $info
	 * @return bool
	 */
	public function insert($table, $info) {
		$table = $this->_prefix . $table;
		try {
			$fields = $this->filter($table, $info);
			$sql = "INSERT INTO  `" . $table . "` (`" . implode($fields, "`, `") . "`) VALUES (:" . implode($fields, ", :") . ");";

			$bind = array();
			foreach ($fields as $field) {
				$bind[":$field"] = $info[$field];
			}

			$stmt = $this->prepare($sql);
			$stmt->execute($bind);
		} catch (PDOException $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
		return $stmt->rowCount();
	}

	/**
	 * @param $table
	 * @param $info
	 * @param $where
	 * @param string $bind
	 * @return bool
	 */
	public function update($table, $info, $where, $bind = "") {
		$table = $this->_prefix . $table;
		$fields = $this->filter($table, $info);
		$fieldSize = sizeof($fields);

		$sql = "UPDATE `" . $table . "` SET ";
		for ($f = 0; $f < $fieldSize; ++$f) {
			if ($f > 0) {
				$sql .= ", ";
			}
			$sql .= $fields[$f] . " = :update_" . $fields[$f];
		}
		$sql .= " WHERE " . $where . ";";

		$bind = $this->cleanup($bind);
		foreach ($fields as $field) {
			$bind[":update_$field"] = $info[$field];
		}

		$stmt = $this->prepare($sql);
		$stmt->execute($bind);
		return $stmt->rowCount();
	}
}
