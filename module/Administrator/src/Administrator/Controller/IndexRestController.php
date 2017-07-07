<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 8/7/16
 * Time: 15:45
 */

namespace Administrator\Controller;

use Application\Controller\AbstractApplicationRestController;
use Zend\View\Model\JsonModel;
use APP\Model;
use APP\Business;

class IndexRestController extends AbstractApplicationRestController {
	public function getList() {
		try {
			$data = array();
			$params = $this->params()->fromQuery();

			$type = isset($params['type']) ? $params['type'] : '';
			$search = isset($params['search']) ? $params['search'] : '';
			$selected_id = isset($params['selected_id']) ? $params['selected_id'] : array();

			if($type) {
				switch ( $type ) {
					case 'tags':
						$data = Business\Movie::getTags(array(
							'search' => $search,
							'selected_id' => $selected_id
						));

						break;
				}
			}

			$result = new JsonModel(array(
				'success' => true,
				'message' => 'success',
				'data' => $data

			));

			return $result;

		} catch ( \Exception $e ) {
			throw new \Exception( $e->getMessage(), $e->getCode() );
		}
	}

	public function get($id)
	{
		try {
			$data = array();
			$params = $this->params()->fromQuery();
			$type = isset($params['type']) ? $params['type'] : '';

			if($type){
				switch ($type){
					case 'tags':
						$movie_tags = Model\User::getMovieTagsByMovieId(array(
							'movie_id' => $id
						));

						if(isset($movie_tags['rows']) && !empty($movie_tags['rows'])){
							$arr_tags = array();
							foreach ($movie_tags['rows'] as $movie_tag){
								if(isset($movie_tag['tag_id'])){
									$arr_tags[] = $movie_tag['tag_id'];
								}
							}

							//
							if(!empty($arr_tags)){
								$tags = Model\User::getTagsById(array(
									'tag_id' => $arr_tags
								));

								$data['rows'] = isset($tags['rows']) ? $tags['rows'] : array();
							}
						}
						break;
				}
			}

			$result = new JsonModel(array(
				'success' => true,
				'message' => 'success',
				'data' => $data

			));

			return $result;


		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	public function create($data)
	{
		try {
			$create = isset($data['create']) ? $data['create'] : '';
			$type = isset($data['type']) ? $data['type'] : '';

			$resp = array();
			if(!empty($type)){
				switch($type){
					case 'tags':
						$resp = Business\Movie::createTag(array(
							'create' => $create
						));
						break;
				}
			}

			$result = new JsonModel(array(
				'success' => true,
				'message' => 'create',
				'data' => $resp

			));

			return $result;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	public function update($id, $data)
	{
		try {
			$type = isset($data['type']) ? $data['type'] : '';

			if($type){
				switch ($type){
					case 'tags':
						$movie_id = isset($data['mid']) ? $data['mid'] : '';
						$tags = isset($data['tags']) ? $data['tags'] : array();

						if($movie_id && $tags){
							$movie_tags = Model\User::getMovieTagsByMovieId(array(
								'movie_id' => $movie_id
							));

							$arr_tags = array();
							if(isset($movie_tags['rows']) && !empty($movie_tags['rows'])){
								foreach ($movie_tags['rows'] as $movie_tag){
									if(isset($movie_tag['tag_id'])){
										$arr_tags[] = $movie_tag['tag_id'];
									}
								}
							}

							foreach ($tags as $tag){
								if(!in_array($tag, $arr_tags)){
									$movie_tag_id = Model\User::createMovieTag(array(
										'movie_id' => $movie_id,
										'tag_id' => $tag,
										'ctime' => date('Y-m-d H:i:s')
									));
								}
							}
						}

						break;
				}
			}

			$result = new JsonModel(array(
				'success' => true,
				'message' => 'update',

			));

			return $result;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}


}
