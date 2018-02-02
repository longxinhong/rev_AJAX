<?php
class session
{
	public $db;
	public $session_table = '';
	public $max_life_time = 180000;
	public $session_id = '';
	public $session_expiry = '';
	public $session_md5 = '';
	public $_time = 0;

	public function __construct(&$db, $session_table, $session_data_table, $session_id = '')
	{
		$this->session($db, $session_table, $session_data_table, $session_id);
	}

	public function session(&$db, $session_table, $session_data_table, $session_id= '')
	{
		$GLOBALS['_SESSION'] = array();
		$this->_time = time();
		
		$this->session_table = $session_table;
		$this->session_data_table = $session_data_table;
		$this->db = &$db;
		
		$this->session_id = $session_id;
		if ($this->session_id) {
		    $this->load_session();
		} 
		else {
		    $this->gen_session_id();
		}
		register_shutdown_function(array(&$this, 'close_session'));
	}

	public function gen_session_id()
	{
		$this->session_id = md5(uniqid(mt_rand(), true));
		return $this->insert_session();
	}

	public function insert_session()
	{
		return $this->db->query('INSERT INTO ' . $this->session_table . ' (sesskey, expiry, data) VALUES (\'' . $this->session_id . '\', \'' . $this->_time . '\', \'a:0:{}\')');
	}

	public function load_session()
	{
		$session = $this->db->getRow('SELECT user_id, user_name, user_rank, discount, email,identity_type, identifier, data, expiry FROM ' . $this->session_table . ' WHERE sesskey = \'' . $this->session_id . '\'');

		if (empty($session)) {
			$this->gen_session_id();
			$this->session_expiry = 0;
			$this->session_md5 = '40cd750bba9870f18aada2478b24840a';
			$GLOBALS['_SESSION'] = array('discount'=>1);
		}
		else {
			if (!empty($session['data']) && (($this->_time - $session['expiry']) <= $this->max_life_time)) {
				$this->session_expiry = $session['expiry'];
				$this->session_md5 = md5($session['data']);
				$GLOBALS['_SESSION'] = unserialize($session['data']);
				$GLOBALS['_SESSION']['user_id'] = $session['user_id'];
				$GLOBALS['_SESSION']['user_name'] = $session['user_name'];
				$GLOBALS['_SESSION']['user_rank'] = $session['user_rank'];
				$GLOBALS['_SESSION']['discount'] = $session['discount'];
				$GLOBALS['_SESSION']['email'] = $session['email'];
				$GLOBALS['_SESSION']['identity_type'] = $session['identity_type'];
				$GLOBALS['_SESSION']['identifier'] = $session['identifier'];
			}
			else {
				$session_data = $this->db->getRow('SELECT data, expiry FROM ' . $this->session_data_table . ' WHERE sesskey = \'' . $this->session_id . '\'');
				if (!empty($session_data['data']) && (($this->_time - $session_data['expiry']) <= $this->max_life_time)) {
					$this->session_expiry = $session_data['expiry'];
					$this->session_md5 = md5($session_data['data']);
					$GLOBALS['_SESSION'] = unserialize($session_data['data']);
					$GLOBALS['_SESSION']['user_id'] = $session['user_id'];
					$GLOBALS['_SESSION']['user_name'] = $session['user_name'];
					$GLOBALS['_SESSION']['user_rank'] = $session['user_rank'];
					$GLOBALS['_SESSION']['discount'] = $session['discount'];
					$GLOBALS['_SESSION']['email'] = $session['email'];
					$GLOBALS['_SESSION']['identity_type'] = $session['identity_type'];
					$GLOBALS['_SESSION']['identifier'] = $session['identifier'];
				}
				else {
					$this->session_expiry = 0;
					$this->session_md5 = '40cd750bba9870f18aada2478b24840a';
					$GLOBALS['_SESSION'] = array('discount'=>1);
				}
			}
		}
	}

	public function update_session()
	{
		$userid = (!empty($GLOBALS['_SESSION']['user_id']) ? intval($GLOBALS['_SESSION']['user_id']) : 0);
		$user_name = (!empty($GLOBALS['_SESSION']['user_name']) ? trim($GLOBALS['_SESSION']['user_name']) : 0);
		$user_rank = (!empty($GLOBALS['_SESSION']['user_rank']) ? intval($GLOBALS['_SESSION']['user_rank']) : 0);
		$discount = (!empty($GLOBALS['_SESSION']['discount']) ? round($GLOBALS['_SESSION']['discount'], 2) : 0);
		$email = (!empty($GLOBALS['_SESSION']['email']) ? trim($GLOBALS['_SESSION']['email']) : 0);
		$identity_type = (!empty($GLOBALS['_SESSION']['identity_type']) ? trim($GLOBALS['_SESSION']['identity_type']) : '');
		$identifier = (!empty($GLOBALS['_SESSION']['identifier']) ? trim($GLOBALS['_SESSION']['identifier']) : '');
		unset($GLOBALS['_SESSION']['user_id']);
		unset($GLOBALS['_SESSION']['user_name']);
		unset($GLOBALS['_SESSION']['user_rank']);
		unset($GLOBALS['_SESSION']['discount']);
		unset($GLOBALS['_SESSION']['email']);
		unset($GLOBALS['_SESSION']['identity_type']);
		unset($GLOBALS['_SESSION']['identifier']);
		$data = serialize($GLOBALS['_SESSION']);
		$this->_time = time();
		if (($this->session_md5 == md5($data)) && ($this->_time < ($this->session_expiry + 10))) {
		    //进10秒有更新就无操作。。
// 			return true;
		}

		$data = addslashes($data);

		if (isset($data[255])) {
			$this->db->autoReplace($this->session_data_table, array('sesskey' => $this->session_id, 'expiry' => $this->_time, 'data' => $data), array('expiry' => "{$this->_time}", 'data' => $data));
			$data = '';
		}
		return $this->db->query('UPDATE ' . $this->session_table . ' SET expiry = \'' . $this->_time . '\', user_id = \'' . $userid . '\', user_name=\'' . $user_name . '\', user_rank=\'' . $user_rank . '\', discount=\'' . $discount . '\', email=\'' . $email . '\', identity_type=\'' . $identity_type . '\', identifier=\'' . $identifier . '\', data = \'' . $data . '\' WHERE sesskey = \'' . $this->session_id . '\' LIMIT 1');
	}

	public function close_session()
	{
		$this->update_session();

		$this->db->query('DELETE FROM ' . $this->session_data_table . ' WHERE expiry < ' . ($this->_time - $this->max_life_time));
		$this->db->query('DELETE FROM ' . $this->session_table . ' WHERE expiry < ' . ($this->_time - $this->max_life_time));
	}

	public function destroy_session()
	{
		$GLOBALS['_SESSION'] = array();
		$this->db->query('DELETE FROM ' . $this->session_data_table . ' WHERE sesskey = \'' . $this->session_id . '\' LIMIT 1');
		$this->db->query('DELETE FROM ' . $this->session_table . ' WHERE sesskey = \'' . $this->session_id . '\' LIMIT 1');
	}

	public function get_session_id()
	{
		return $this->session_id;
	}

	public function get_users_count()
	{
		return $this->db->getOne('SELECT count(*) FROM ' . $this->session_table);
	}
}

?>
