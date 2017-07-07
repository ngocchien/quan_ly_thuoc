<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 5/31/17
 * Time: 11:51 PM
 */

namespace Administrator\Controller;

use APP\Controller\MyController;
use APP\Business;
use APP\Model;

class BannerController extends MyController {

	public function indexAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $params['not_status'] = Model\Banner::BANNER_STATUS_REMOVE;

        //get list
        $user_id = $users = $file_id = $files = [];
        $banners = Business\Banner::getList($params);
        if (!empty($banners['rows'])) {
            foreach ($banners['rows'] as $row) {
                $file_id[] = $row['fid'];
                $user_id[] = $row['user_created'];
            }
        }

        if(!empty($user_id)){
            $result = Model\User::getUser([
                'in_user_id' => $user_id,
                'limit' => 100,
                'offset' => 0
            ]);

            if(!empty($result['rows'])){
                foreach ($result['rows'] as $row){
                    $users[$row['user_id']] = $row;
                }
            }
        }

        if(!empty($file_id)){
            $result = Model\Upload::get([
                'IN_FID' => array_values(array_unique($file_id)),
                'limit' => 100,
                'offset' => 0
            ]);

            if(!empty($result['rows'])){
                foreach ($result['rows'] as $row){
                    $files[$row['fid']] = $row;
                }
            }
        }

        return [
            'params' => $params,
            'banners' => $banners,
            'arr_status' => Model\Banner::renderStatus(),
            'users' => $users,
            'files' => $files
        ];
	}

	public function createAction(){
        $params = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $result = Business\Banner::create($params);
            if (!empty($result['success'])) {
                return $this->redirect()->toRoute('administratorMenu', ['action' => 'edit', 'id' => $result['menu_id']]);
            }
        }

        return [
            'params' => $params,
            'arr_status' => Model\Banner::renderStatus()
        ];
    }

    public function editAction(){
        $params = $this->params()->fromRoute();
        $id = $params['id'];

        if(empty($id)){
            return $this->redirect()->toRoute('administrator');
        }

        //check exist
        $result = Business\Banner::getList([
            'banner_id' => $id,
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE,
            'limit' => 1,
            'page' => 1
        ]);

        if(empty($result['rows'])){
            return $this->redirect()->toRoute('administrator');
        }

        $banner = $result['rows'][0];

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $params['banner_id'] = $id;
            $params = Business\Banner::update($params);
            if(!empty($params['success'])){
                return $this->redirect()->toRoute('administratorBanner', ['action' => 'edit', 'id' => $id]);
            }
        }

        //get url image
        $result = Model\Upload::get([
            'FID' => $banner['fid']
        ]);

        $image = $result['rows'][0];

        return [
            'params' => $params,
            'banner' => $banner,
            'render_status' => Model\Banner::renderStatus(),
            'image' => $image
        ];
    }

    public function deleteAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Banner::delete($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }
}