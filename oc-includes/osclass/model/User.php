<?php if ( !defined('ABS_PATH') ) exit('ABS_PATH is not loaded. Direct access is not allowed.');

    /*
     *      Osclass – software for creating and publishing online classified
     *                           advertising platforms
     *
     *                        Copyright (C) 2012 OSCLASS
     *
     *       This program is free software: you can redistribute it and/or
     *     modify it under the terms of the GNU Affero General Public License
     *     as published by the Free Software Foundation, either version 3 of
     *            the License, or (at your option) any later version.
     *
     *     This program is distributed in the hope that it will be useful, but
     *         WITHOUT ANY WARRANTY; without even the implied warranty of
     *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *             GNU Affero General Public License for more details.
     *
     *      You should have received a copy of the GNU Affero General Public
     * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
     */

    /**
     * User DAO
     */
    class User extends DAO
    {
        /**
         *
         * @var type
         */
        private static $instance;

        public static function newInstance()
        {
            if( !self::$instance instanceof self ) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         *
         */
        function __construct()
        {
            parent::__construct();
            $this->setTableName('t_user');
            $this->setPrimaryKey('pk_i_id');
            $array_fields = array(
                'pk_i_id',
                'dt_reg_date',
                'dt_mod_date',
                's_name',
                's_password',
                's_secret',
                's_username',
                's_email',
                's_website',
                's_phone_land',
                's_phone_mobile',
                'b_enabled',
                'b_active',
                's_pass_code',
                's_pass_date',
                's_pass_ip',
                'fk_c_country_code',
                's_country',
                's_address',
                's_zip',
                'fk_i_region_id',
                's_region',
                'fk_i_city_id',
                's_city',
                'fk_i_city_area_id',
                's_city_area',
                'd_coord_lat',
                'd_coord_long',
                'b_company',
                'i_items',
                'i_comments',
                'dt_access_date',
                's_access_ip'
            );
            $this->setFields($array_fields);
        }

        /**
         * Find an user by its primary key
         *
         * @access public
         * @since 2.3.2
         * @param string $term
         * @return array
         */
        public function ajax($query = '')
        {
            $this->dao->select('pk_i_id as id, CONCAT(s_name, \' (\', s_email , \')\') as label, s_name as value');
            $this->dao->from($this->getTableName());
            $this->dao->like('s_name', $query, 'after');
            $this->dao->orLike('s_email', $query, 'after');
            $this->dao->limit(0, 10);

            $result = $this->dao->get();

            if( $result == false ) {
                return array();
            }

            return $result->result();
        }


        /**
         * Find an user by its primary key
         *
         * @access public
         * @since unknown
         * @param int $id
         * @param string $locale
         * @return array
         */
        public function findByPrimaryKey($id, $locale = null)
        {
            $this->dao->select();
            $this->dao->from($this->getTableName());
            $this->dao->where($this->getPrimaryKey(), $id);
            $result = $this->dao->get();
            if($result == false) {
                return array();
            }

            if( $result->numRows() != 1 ) {
                return array();
            }

            return $this->extendData($result->row(), $locale);
        }

        /**
         * Find an user by its email
         *
         * @access public
         * @since unknown
         * @param string $email
         * @return array
         */
        public function findByEmail($email, $locale = null)
        {
            $this->dao->select();
            $this->dao->from($this->getTableName());
            $this->dao->where('s_email', $email);
            $result = $this->dao->get();

            if( $result == false ) {
                return false;
            } else if($result->numRows() == 1){
                return $this->extendData($result->row(), $locale);
            } else {
                return array();
            }
        }

        /**
         * Find an user by its username
         *
         * @access public
         * @since 3.1
         * @param string $username
         * @return array
         */
        public function findByUsername($username, $locale = null)
        {
            $this->dao->select();
            $this->dao->from($this->getTableName());
            $this->dao->where('s_username', $username);
            $result = $this->dao->get();

            if( $result == false ) {
                return $this->findByUsernameLDAP($username, $locale);
            } else if($result->numRows() == 1){
                return $this->extendData($result->row(), $locale);
            } else {
                return $this->findByUsernameLDAP($username, $locale);
            }
        }

        /**
         * Find an user by its username in the LDAP
         *
         * @access public
         * @since 3.1
         * @param string $username
         * @return array
         */
        public function findByUsernameLDAP($username, $locale)
        {
            // if ldap is found, insert it into the database

            $ldap = LDAP::getConnection();

            if ($ldap) {
                if ($ldap->bindAdmin(LDAP_ADMIN_PASSWORD) && $ldap->getUserEntry($username)) {
                    $input = array();

                    $input['s_secret']    = osc_genRandomPassword();
                    $input['dt_reg_date'] = date('Y-m-d H:i:s');

                    $input['s_email'] = $ldap->getInternalEmail($username);

                    $pw = $ldap->getPassword($username);
                    $pw = sha1($pw);

                    $input['s_password'] = $pw;
                    $input['s_username']     = $username;

                    $input['s_name']         = $username;
                    $input['s_website']      = Params::getParam('s_website');
                    $input['s_phone_land']   = Params::getParam('s_phone_land');
                    $input['s_phone_mobile'] = Params::getParam('s_phone_mobile');

                    //locations...
                    $country = Country::newInstance()->findByCode( Params::getParam('countryId') );
                    if(count($country) > 0) {
                        $countryId   = $country['pk_c_code'];
                        $countryName = $country['s_name'];
                    } else {
                        $countryId   = null;
                        $countryName = Params::getParam('country');
                    }

                    if( intval( Params::getParam('regionId') ) ) {
                        $region = Region::newInstance()->findByPrimaryKey( Params::getParam('regionId') );
                        if( count($region) > 0 ) {
                            $regionId   = $region['pk_i_id'];
                            $regionName = $region['s_name'];
                        }
                    } else {
                        $regionId   = null;
                        $regionName = Params::getParam('region');
                    }

                    if( intval( Params::getParam('cityId') ) ) {
                        $city = City::newInstance()->findByPrimaryKey( Params::getParam('cityId') );
                        if( count($city) > 0 ) {
                            $cityId   = $city['pk_i_id'];
                            $cityName = $city['s_name'];
                        }
                    } else {
                        $cityId   = null;
                        $cityName = Params::getParam('city');
                    }

                    $input['fk_c_country_code'] = $countryId;
                    $input['s_country'] = $countryName;
                    $input['fk_i_region_id'] = $regionId;
                    $input['s_region']       = $regionName;
                    $input['fk_i_city_id']   = $cityId;
                    $input['s_city']         = $cityName;
                    $input['s_city_area']    = Params::getParam('cityArea');
                    $input['s_address']      = Params::getParam('address');
                    $input['b_company']      = (Params::getParam('b_company') != '' && Params::getParam('b_company') != 0) ? 1 : 0;
                    $input['b_active']      = 1;

                    $this->insert($input);
                    $userId = $this->dao->insertedId();

                    Log::newInstance()->insertLog('user', 'addfromldap', $userId, $input['s_email'], 'user', $userId);

                    // TODO: from oc-includes/osclass/UserActions.php, add all the hooks so that the fire-event model presumably for the plugins work correctly

                    $ldap->unbind();
                }

            }

            $this->dao->select();
            $this->dao->from($this->getTableName());
            $this->dao->where('s_username', $username);
            $result = $this->dao->get();

            if( $result == false ) {
                return false;
            } else if($result->numRows() == 1){
                return $this->extendData($result->row(), $locale);
            } else {
                return array();
            }

        }

        /**
         * Find an user by its id and password
         *
         * @access public
         * @since unknown
         * @param string $key
         * @param string $password
         * @return array
         */
        public function findByCredentials($key, $password, $locale = null)
        {
            $this->dao->select();
            $this->dao->from($this->getTableName());
            $conditions = array(
                's_email'   => $key,
                's_password'=> sha1($password)
            );
            $this->dao->where($conditions);
            $result = $this->dao->get();

            if( $result == false ) {
                return false;
            } else if($result->numRows() == 1){
                return $this->extendData($result->row(), $locale);
            } else {
                return array();
            }
        }

        /**
         * Find an user by its id and secret
         *
         * @access public
         * @since unknown
         * @param string $id
         * @param string $secret
         */
        public function findByIdSecret($id, $secret, $locale = null)
        {
            $this->dao->select();
            $this->dao->from($this->getTableName());
            $conditions = array(
                'pk_i_id'  => $id,
                's_secret' => $secret
            );
            $this->dao->where($conditions);
            $result = $this->dao->get();

            if( $result == false ) {
                return false;
            } else if($result->numRows() == 1){
                return $this->extendData($result->row(), $locale);
            } else {
                return array();
            }
        }

        /**
         *
         *
         * @access public
         * @since unknown
         * @param string $id
         * @param string $secret
         * @return array
         */
        public function findByIdPasswordSecret($id, $secret, $locale = null)
        {
            if($secret=='') { return null; }
            $date = date("Y-m-d H:i:s", (time()-(24*3600)));
            $this->dao->select();
            $this->dao->from($this->getTableName());
            $conditions = array(
                'pk_i_id'       => $id,
                's_pass_code'   => $secret
            );
            $this->dao->where($conditions);
            $this->dao->where("s_pass_date >= '$date'");
            $result = $this->dao->get();

           if( $result == false ) {
                return false;
            } else if($result->numRows() == 1){
                return $this->extendData($result->row(), $locale);
            } else {
                return array();
            }
        }

        /**
         * Add description to user array
         *
         * @since 3.1.1
         * @param $row with user's info
         * @return array
         */
        private function extendData($user, $locale = null) {
            $this->dao->select();
            $this->dao->from(DB_TABLE_PREFIX.'t_user_description');
            $this->dao->where('fk_i_user_id', $user['pk_i_id']);
            if(!is_null($locale)) {
                $this->dao->where('fk_c_locale_code', $locale);
            }
            $result = $this->dao->get();
            $descriptions = $result->result();

            $user['locale'] = array();
            foreach($descriptions as $sub_row) {
                $user['locale'][$sub_row['fk_c_locale_code']] = $sub_row;
            }
            return $user;
        }


/**
         * Delete an user given its id
         *
         * @access public
         * @since unknown
         * @param int $id
         * @return bool
         */
        public function deleteUser($id = null)
        {
            if($id!=null) {
                osc_run_hook('delete_user', $id);

                $this->dao->select('pk_i_id, fk_i_category_id');
                $this->dao->from(DB_TABLE_PREFIX."t_item");
                $this->dao->where('fk_i_user_id', $id);
                $result = $this->dao->get();
                $items = $result->result();

                $itemManager = Item::newInstance();
                foreach($items as $item) {
                    $itemManager->deleteByPrimaryKey($item['pk_i_id']);
                }

                ItemComment::newInstance()->delete(array('fk_i_user_id' => $id));

                $this->dao->delete(DB_TABLE_PREFIX.'t_user_email_tmp', array('fk_i_user_id' => $id));
                $this->dao->delete(DB_TABLE_PREFIX.'t_user_description', array('fk_i_user_id' => $id));
                $this->dao->delete(DB_TABLE_PREFIX.'t_alerts', array('fk_i_user_id' => $id));
                return $this->dao->delete($this->getTableName(), array('pk_i_id' => $id));
            }
            return false;
        }

        /**
         * Insert users' description
         *
         * @access private
         * @since unknown
         * @param int $id
         * @param string $locale
         * @param string $info
         * @return array
         */
        private function insertDescription($id, $locale, $info)
        {
            $array_set = array(
                'fk_i_user_id'      => $id,
                'fk_c_locale_code'  => $locale,
                's_info'            => $info
            );

            return $this->dao->insert(DB_TABLE_PREFIX.'t_user_description', $array_set);
        }

        /**
         * Update users' description
         *
         * @access public
         * @since unknown
         * @param int $id
         * @param string $locale
         * @param string $info
         * @return bool
         */
        public function updateDescription($id, $locale, $info)
        {
            $conditions = array('fk_c_locale_code' => $locale, 'fk_i_user_id' => $id);
            $exist = $this->existDescription($conditions);

            if(!$exist) {
                $result = $this->insertDescription($id, $locale, $info);
                return $result;
            }

            $array_where = array(
                'fk_c_locale_code'  => $locale,
                'fk_i_user_id'      => $id
            );
            return $this->dao->update(DB_TABLE_PREFIX.'t_user_description', array('s_info'    => $info), $array_where);
        }

        /**
         * Check if a description exists
         *
         * @access private
         * @since unknown
         * @param array $conditions
         * @return bool
         */
        private function existDescription($conditions)
        {
            $this->dao->select();
            $this->dao->from(DB_TABLE_PREFIX.'t_user_description');
            $this->dao->where($conditions);

            $result = $this->dao->get();

            if( $result == false || $result->numRows() == 0) {
                return false;
            } else {
                return true;
            }

            return (bool) $result;
        }


        /**
         * Return list of users
         *
         * @access public
         * @since 2.4
         * @param int $start
         * @param int $end
         * @param string $order_column
         * @param string $order_direction
         * @parma string $name
         * @return array
         */
        public function search($start = 0, $end = 10, $order_column = 'pk_i_id', $order_direction = 'DESC', $name = '')
        {
            // SET data, so we always return a valid object
            $users = array();
            $users['rows']          = 0;
            $users['total_results'] = 0;
            $users['users']         = array();

            $this->dao->select('SQL_CALC_FOUND_ROWS *');
            $this->dao->from($this->getTableName());
            $this->dao->orderBy($order_column, $order_direction);
            $this->dao->limit($start, $end);
            if( $name != '' ) {
                $this->dao->like('s_name', $name);
            }
            $rs = $this->dao->get();

            if( !$rs ) {
                return $users;
            }

            $users['users'] = $rs->result();

            $rsRows = $this->dao->query('SELECT FOUND_ROWS() as total');
            $data   = $rsRows->row();
            if( $data['total'] ) {
                $users['total_results'] = $data['total'];
            }

            $rsTotal = $this->dao->query('SELECT COUNT(*) as total FROM '.$this->getTableName());
            $data   = $rsTotal->row();
            if( $data['total'] ) {
                $users['rows'] = $data['total'];
            }

            return $users;
        }

        /**
         * Return list of users by email
         *
         * @access public
         * @since 2.4
         * @param int $start
         * @param int $end
         * @param string $order_column
         * @param string $order_direction
         * @parma string $email
         * @return array
         */
        public function searchByEmail($start = 0, $end = 10,  $email = '', $order_column = 'pk_i_id', $order_direction = 'DESC')
        {
            return $this->_search('s_email', $email, $start, $end, $order_column, $order_direction);
        }

        /**
         * Return list of users by user id
         *
         * @access public
         * @since 2.4
         * @param int $start
         * @param int $end
         * @param string $order_column
         * @param string $order_direction
         * @parma string $userId
         * @return array
         */
        public function searchByPrimaryKey($start = 0, $end = 10,  $userId = '', $order_column = 'pk_i_id', $order_direction = 'DESC')
        {
            return $this->_search('pk_i_id', $userId, $start, $end, $order_column, $order_direction);
        }

        private function _search($field , $value, $start = 0, $end = 10, $order_column = 'pk_i_id', $order_direction = 'DESC')
        {
            // SET data, so we always return a valid object
            $users = array();
            $users['rows']          = 0;
            $users['total_results'] = 0;
            $users['users']         = array();

            $this->dao->select('SQL_CALC_FOUND_ROWS *');
            $this->dao->from($this->getTableName());
            $this->dao->orderBy($order_column, $order_direction);
            $this->dao->limit($start, $end);

            if($field == 'pk_i_id') {
                $this->dao->where('pk_i_id', $value);
            } else if($field == 's_email') {
                $this->dao->where('s_email', $value);
            }

            $rs = $this->dao->get();

            if( !$rs ) {
                return $users;
            }

            $users['users'] = $rs->result();

            $rsRows = $this->dao->query('SELECT FOUND_ROWS() as total');
            $data   = $rsRows->row();
            if( $data['total'] ) {
                $users['total_results'] = $data['total'];
            }

            $rsTotal = $this->dao->query('SELECT COUNT(*) as total FROM '.$this->getTableName());
            $data   = $rsTotal->row();
            if( $data['total'] ) {
                $users['rows'] = $data['total'];
            }

            return $users;
        }

        /**
         * Return number of users
         *
         * @since 2.3.6
         * @return int
         */
        public function countUsers($condition = 'b_enabled = 1 AND b_active = 1')
        {
            $this->dao->select("COUNT(*) as i_total");
            $this->dao->from(DB_TABLE_PREFIX.'t_user');
            $this->dao->where($condition);

            $result = $this->dao->get();

            if( $result == false || $result->numRows() == 0) {
                return 0;
            }

            $row = $result->row();
            return $row['i_total'];
        }

        /**
         * Insert last access data
         *
         * @param int $userId
         * @param datetime $date
         * @param string $ip
         *
         * @return boolean on success
         */
        function lastAccess($userId, $date, $ip, $time = NULL) {
            if($time!=null) {
                $this->dao->select("dt_access_date, s_access_ip");
                $this->dao->from(DB_TABLE_PREFIX.'t_user');
                $this->dao->where('pk_i_id', $userId);
                $this->dao->where("dt_access_date <= '" . (date('Y-m-d H:i:s', time()-$time))."'");
                $result = $this->dao->get();
                if( $result == false || $result->numRows() == 0) {
                    return false;
                }
            }
            return $this->update(array('dt_access_date' => $date, 's_access_ip' => $ip), array('pk_i_id' => $userId));
        }

        /**
         * Increase number of items, given a user id
         *
         * @access public
         * @since unknown
         * @param int $id user id
         * @return int number of affected rows, id error occurred return false
         */
        public function increaseNumItems($id)
        {
            if(!is_numeric($id)) {
                return false;
            }

            $sql = sprintf('UPDATE %s SET i_items = i_items + 1 WHERE pk_i_id = %d', $this->getTableName(), $id);
            return $this->dao->query($sql);
        }

        /**
         * Decrease number of items, given a user id
         *
         * @access public
         * @since unknown
         * @param int $id user id
         * @return int number of affected rows, id error occurred return false
         */
        public function decreaseNumItems($id)
        {
            if(!is_numeric($id)) {
                return false;
            }

            $sql = sprintf('UPDATE %s SET i_items = i_items - 1 WHERE pk_i_id = %d', $this->getTableName(), $id);
            return $this->dao->query($sql);
        }
    }

    /* file end: ./oc-includes/osclass/model/User.php */
?>
