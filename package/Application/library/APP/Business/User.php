<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 03/06/2017
 * Time: 08:50
 */
namespace APP\Business;

use APP\Model;
use Zend\Authentication\AuthenticationService;
use Zend\Validator\EmailAddress;
use Zend\Mvc\Controller\AbstractActionController;

class User
{
    public static function checkLogin($params){
        if(empty($params['username']) || empty($params['password'])){
            $params['error'] = 'Vui lòng nhập đầy đủ username và password!';
            return $params;
        }
        $remember = !empty($params['remember']) ? 1 : 0;

        $username = trim(strip_tags($params['username']));
        $password = trim(strip_tags($params['password']));

        $users = Model\User::getUser([
            'user_name' => $username,
            'status' => Model\User::USER_STATUS_ACTIVE,
            'password' => md5($password)
        ]);

        if(empty($users['total'])){
            $params['error'] = 'Không tìm thấy thông tin user!';
            return $params;
        }

        //
        $user = $users['rows'][0];

        //update last login
        $updated = Model\User::updateUser([
            'last_login_date' => time()
//            'user_login_ip' => AbstractActionController::
        ], $user['user_id']);

        if(!$updated){
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
            return $params;
        }

        //get group
        $groups = Model\Group::get([
            'group_id' => $user['group_id'],
            'limit' => 1,
            'offset' => 0
        ]);
        $group = $groups['rows'][0];

        //get permission
        $permissions = Model\Permission::get([
            'or_group_id' => $user['group_id'],
            'or_user_id' => $user['user_id'],
            'not_status' => Model\Permission::PERMISSION_STATUS_REMOVE,
            'limit' => 1000,
            'offset' => 0
        ]);

        $user['is_acp'] = $group['is_acp'];
        $user['is_full_access'] = $group['is_full_access'];
        $user['permission'] = $permissions['rows'];

        $auth = new AuthenticationService();
        $auth->clearIdentity();
        $auth->getStorage()->write($user);

        if ($remember == 1) {

        }

        return [
            'success' => true
        ];
    }

    public static function createUser($params){
        if(empty($params['user_name']) || empty($params['password']) || empty($params['full_name'])){
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $user_name = trim(strip_tags($params['user_name']));
        $password = trim(strip_tags($params['password']));
        $full_name = trim(strip_tags($params['full_name']));
        $email = trim(strip_tags($params['email']));
        $group_id = $params['group_id'];

        //check length
        if(strlen($user_name) < 6){
            $params['error_user_name'][] = 'user name phải từ 6 ký tự trở lên!';
            return $params;
        }

        if(strlen($password) < 6){
            $params['error_password'][] = 'password phải từ 6 ký tự trở lên!';
            return $params;
        }

        //check user name is exist DB
        $users = Model\User::getUser([
            'user_name' =>$user_name,
            'not_status' => Model\User::USER_STATUS_REMOVE
        ]);

        if(!empty($users['rows'])){
            $params['error'] = 'User name này đã tồn tại trong hệ thống!</br> Vui lòng chọn user name khác!';
            return $params;
        }

        //validate email
        $validate = new EmailAddress();
        if(!$validate->isValid($email)){
            $params['error_email'] = 'Địa chỉ email không hợp lệ!';
            return $params;
        }

        //check email
        $exist_email = Model\User::getUser([
            'email' =>$email,
            'not_status' => Model\User::USER_STATUS_REMOVE
        ]);

        if(!empty($exist_email['rows'])){
            $params['error_email'] = 'Email này đã tồn tại trong hệ thống! Vui lòng kiểm tra lại!';
            return $params;
        }
        $user_id = Model\User::createUser([
            'user_name' => $user_name,
            'password' => md5($password),
            'full_name' => $full_name,
            'created_date' => time(),
            'email' => $email,
            'status' => (int)$params['status'],
            'group_id' => $group_id
        ]);

        if(!$user_id){
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        return [
            'success' => true,
            'user_id' => $user_id
        ];

    }

    public static function userLogout(){
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        return true;
    }

    public static function getUSer($params){
        $limit = empty($params['limit']) ? 10 : (int)$params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $users = Model\User::getUser($params);
        return $users;
    }

    public static function updateUser($params,$user_id){
        if(empty($params['email']) || empty($params['full_name'])){
            $params['error'] = 'Vui lòng nhập đầy đủ thông tin';
        }

        $full_name = trim(strip_tags($params['full_name']));
        $email = trim(strip_tags($params['email']));
        $group_id = $params['group_id'];

        //validate email
        $validate = new EmailAddress();
        if(!$validate->isValid($email)){
            $params['error_email'] = 'Địa chỉ email không hợp lệ!';
            return $params;
        }

        //check email
        $exist_email = Model\User::getUser([
            'email' =>$email,
            'not_status' => Model\User::USER_STATUS_REMOVE,
            'not_user_id' => $user_id
        ]);

        if(!empty($exist_email['rows'])){
            $params['error_email'] = 'Email này đã tồn tại trong hệ thống! Vui lòng kiểm tra lại!';
            return $params;
        }
        $status = Model\User::updateUser([
            'full_name' => $full_name,
            'updated_date' => time(),
            'email' => $email,
            'user_updated' => USER_ID,
            'status' => (int)$params['status'],
            'group_id' => $group_id
        ], $user_id);

        if(!$status){
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        return [
            'success' => true
        ];
    }

    public static function deleteUser($params){

        if(empty($params['user_id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $user_id = (int) $params['user_id'];

        //get info user
        $users = Model\User::getUser([
            'user_id' => $user_id,
            'not_status' => Model\User::USER_STATUS_REMOVE,
            'not_user_id' => Model\User::USER_ID_SUPPER_ADMIN
        ]);

        if(empty($users['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        //delete
        $status = Model\User::updateUser([
            'status' => Model\User::USER_STATUS_REMOVE,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],$user_id);

        if(!$status){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        return [
            'st' => 1,
            'ms' => 'Xóa người dùng thành công!'
        ];
    }
}