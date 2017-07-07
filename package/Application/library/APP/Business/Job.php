<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 2/19/17
 * Time: 10:25 PM
 */

namespace APP\Business;

use APP\Model;
use APP\Utils;
use APP\Nosql;
use APP\Business;

class Job
{
    public static function publishMovie($params){
        date_default_timezone_set("Asia/Kolkata");
        $limit = isset($params['limit']) ? $params['limit'] : 10;
        $offset = 0;

        $ch = @curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ads.tuando.me/search?type=crawler&status=2&is_posted=0&all=true&page=". $offset ."&limit=". $limit);
        $head[] = "Connection: keep-alive";
        $head[] = "Keep-Alive: 300";
        $head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $head[] = "Accept-Language: en-us,en;q=0.5";

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $movies = json_decode(curl_exec($ch), true);

        if(isset($movies['data']) && isset($movies['data']['data']) && !empty($movies['data']['data'])){
            foreach ($movies['data']['data'] as $movie){
                if(isset($movie['MOVIE_ID']) && isset($movie['IS_POSTED']) && $movie['IS_POSTED'] == 0){
                    //
                    self::createMovie($movie);
                }
            }
        }

        curl_close($ch);
    }

    public static function createMovie($params){
        date_default_timezone_set("Asia/Kolkata");
        if(isset($params['MOVIE_ID'])){
            $mid = isset($params['MOVIE_ID']) ? $params['MOVIE_ID'] : '';
            $movie_name = isset($params['MOVIE_NAME']) ? $params['MOVIE_NAME'] : '';
            $movie_name_en = isset($params['MOVIE_NAME_EN']) ? $params['MOVIE_NAME_EN'] : '';
            $genres = isset($params['GENRE']) ? $params['GENRE'] : '';
            $directors = isset($params['DIRECTOR']) ? $params['DIRECTOR'] : '';
            $actors = isset($params['ACTOR']) ? $params['ACTOR'] : '';
            $country = isset($params['COUNTRY']) ? $params['COUNTRY'] : '';
            $poster = isset($params['POSTER']) ? $params['POSTER'] : '';
            $cover = isset($params['COVER']) ? $params['COVER'] : '';
            $description = isset($params['DESCRIPTION']) ? $params['DESCRIPTION'] : '';
            $runtime = isset($params['RUNTIME']) ? $params['RUNTIME'] : '';
            $year = isset($params['YEAR']) ? $params['YEAR'] : '';
            $trailer = isset($params['TRAILER']) ? $params['TRAILER'] : '';
            $shorten = isset($params['SHORTEN']) ? $params['SHORTEN'] : '';
            $link = isset($params['LINK']) ? $params['LINK'] : '';
            $movie_type = isset($params['MOVIE_TYPE']) ? $params['MOVIE_TYPE'] : '';
            $website_type = isset($params['WEBSITE_TYPE']) ? $params['WEBSITE_TYPE'] : '';

            if($movie_name || $movie_name_en){
                switch ($website_type){
                    case Model\Movie::WEBSITE_APHIM:
                        $genres = json_decode($genres, true);
                        $arr_category_id = array();
                        if(!empty($genres)){
                            foreach ($genres as $genre){

                                if(isset($genre['name_vi'])){
                                    $category = Business\Movie::getSearchCategory(array(
                                        'search' => $genre['name_vi'],
                                        'selected_id' => $arr_category_id
                                    ));

                                    if(isset($category['rows']) && !empty($category['rows'])){
                                        foreach ($category['rows'] as $cate){
                                            if(isset($cate['id'])){
                                                $arr_category_id[] = $cate['id'];
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        //
                        $country = json_decode($country, true);
                        $arr_country_id = array();
                        if(!empty($country)){
                            foreach ($country as $item){
                                if(isset($item['title'])){
                                    $countries = Business\Movie::getCountry(array(
                                        'search' => $item['title'],
                                        'selected_id' => $arr_country_id
                                    ));

                                    if(isset($countries['rows']) && !empty($countries['rows'])){
                                        foreach ($countries['rows'] as $cou){
                                            if(isset($cou['id'])){
                                                $arr_country_id[] = $cou['id'];
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        //
                        $directors = json_decode($directors, true);
                        $arr_director_id = array();
                        if(!empty($directors)){
                            foreach ($directors as $director_name){
                                $director = Business\Movie::createDirector(array(
                                    'create' => $director_name
                                ));

                                if(!empty($director)){
                                    foreach ($director as $dir){
                                        if(isset($dir['id'])){
                                            $arr_director_id[] = $dir['id'];
                                        }
                                    }
                                }
                            }
                        }

                        //
                        $actors = json_decode($actors, true);
                        $arr_actor_id = array();
                        if(!empty($actors)){
                            foreach ($actors as $actor_name){
                                $actor = Business\Movie::createActor(array(
                                    'create' => $actor_name
                                ));

                                if(!empty($actor)){
                                    foreach ($actor as $act){
                                        if(isset($act['id'])){
                                            $arr_actor_id[] = $act['id'];
                                        }
                                    }
                                }
                            }
                        }

                        //
                        $poster = json_decode($poster, true);
                        $poster_id = 0;
                        if(isset($poster['original'])){
                            $poster_img = Business\Upload::uploadFromUrl(array(
                                'href' => $poster['original'],
                                'file_name' => $shorten . '-poster'
                            ));

                            if(isset($poster_img['big'])){
                                $poster_id = isset($poster_img['big']['fid']) ? $poster_img['big']['fid'] : 0;
                            }
                        }

                        //
                        $cover = json_decode($cover, true);
                        $cover_id = 0;
                        if(isset($cover['original'])){
                            $cover_img = Business\Upload::uploadFromUrl(array(
                                'href' => $cover['original'],
                                'file_name' => $shorten . '-cover'
                            ));

                            if(isset($cover_img['big'])){
                                $cover_id = isset($cover_img['big']['fid']) ? $cover_img['big']['fid'] : 0;
                            }
                        }

                        //Create movie
                        $arr_input = array(
                            'movie_name' => $movie_name ? $movie_name : $movie_name_en,
                            'movie_name_en' => $movie_name_en,
                            'movie_shorten' => mb_strtolower(Utils::url_title(Utils::shortenString($movie_name ? $movie_name : $movie_name_en))),
                            'movie_type' => $movie_type,
                            'content' => $description,
                            'status' => TYPE_STATUS_INVISIBLE,
                            'status_name' => '',
                            'times' => $runtime,
                            'year' => $year,
                            'fid' => $poster_id,
                            'cover' => $cover_id,
                            'trailer_url' => $trailer,
                            'movie_theaters' => 0,
                            'carousel' => 0,
                            'ctime' => date('Y-m-d H:i:s')
                        );

                        $movie_id = Model\User::createMovie($arr_input);

                        if($movie_id){
                            echo "Movies: ". $movie_id . " - ". $movie_name ? $movie_name : $movie_name_en ."\n";

                            //Index Elastic
                            Model\ElasticSearch::index('movies', array_merge(array(
                                'mid' => $movie_id,
                            ), $arr_input));

                            //Add category
                            if(!empty($arr_category_id)){
                                foreach($arr_category_id as $cat_id){
                                    $movie_category_id = Model\User::createMovieCategory(array(
                                        'movie_id' => $movie_id,
                                        'category_id' => $cat_id,
                                        'ctime' => date('Y-m-d H:i:s')
                                    ));

                                    //Index elastic
                                    if($movie_category_id){
                                        Model\ElasticSearch::index('movie_category', array(
                                            'movie_category_id' => $movie_category_id,
                                            'movie_id' => $movie_id,
                                            'category_id' => $cat_id,
                                            'ctime' => date('Y-m-d H:i:s')
                                        ));
                                    }
                                }
                            }

                            //Add director
                            if(!empty($arr_director_id)){
                                //Add Director for mid
                                foreach($arr_director_id as $director_id){
                                    $movie_director_id = Model\User::createMovieDirector(array(
                                        'movie_id' => $movie_id,
                                        'director_id' => $director_id,
                                        'ctime' => date('Y-m-d H:i:s')
                                    ));

                                    //Index elastic
                                    if($movie_director_id){
                                        Model\ElasticSearch::index('movie_director', array(
                                            'movie_director_id' => $movie_director_id,
                                            'movie_id' => $movie_id,
                                            'director_id' => $director_id,
                                            'ctime' => date('Y-m-d H:i:s')
                                        ));
                                    }
                                }
                            }

                            //Add actor
                            if(!empty($arr_actor_id)){
                                //Add Actor for mid
                                foreach($arr_actor_id as $actor_id){
                                    $movie_actor_id = Model\User::createMovieActor(array(
                                        'movie_id' => $movie_id,
                                        'actor_id' => $actor_id,
                                        'ctime' => date('Y-m-d H:i:s')
                                    ));

                                    //Index elastic
                                    if($movie_actor_id){
                                        Model\ElasticSearch::index('movie_actor', array(
                                            'movie_actor_id' => $movie_actor_id,
                                            'movie_id' => $movie_id,
                                            'actor_id' => $actor_id,
                                            'ctime' => date('Y-m-d H:i:s')
                                        ));
                                    }
                                }
                            }

                            //Add country
                            if(!empty($arr_country_id)){
                                //Add Country for mid
                                foreach($arr_country_id as $country_id){
                                    $movie_country_id = Model\User::createMovieCountry(array(
                                        'movie_id' => $movie_id,
                                        'country_id' => $country_id,
                                        'ctime' => date('Y-m-d H:i:s')
                                    ));

                                    //Index elastic
                                    if($movie_country_id){
                                        Model\ElasticSearch::index('movie_country', array(
                                            'movie_country_id' => $movie_country_id,
                                            'movie_id' => $movie_id,
                                            'country_id' => $country_id,
                                            'ctime' => date('Y-m-d H:i:s')
                                        ));
                                    }
                                }
                            }

                            //Update episode
                            switch ($movie_type){
                                case Model\Movie::MOVIE_TYPE_SINGLE:
                                    if(!empty($link)){
                                        //Add episode for mid
                                        $movie_episode_id = Model\User::createMovieEpisode(array(
                                            'movie_id' => $movie_id,
                                            'episode' => 1,
                                            'position' => 1,
                                            'times' => $runtime,
                                            'link' => $link,
                                            'ctime' => date('Y-m-d H:i:s')
                                        ));

                                        //Index elastic
                                        if($movie_episode_id){
                                            Model\ElasticSearch::index('movie_episode', array(
                                                'movie_episode_id' => $movie_episode_id,
                                                'movie_id' => $movie_id,
                                                'episode' => 1,
                                                'position' => 1,
                                                'times' => $runtime,
                                                'link' => $link,
                                                'ctime' => date('Y-m-d H:i:s')
                                            ));
                                        }
                                    }
                                    break;
                            }

                            //Update is posted
                            self::updateIsPosted(array(
                                'movie_id' => $mid
                            ));
                        }
                        break;
                }
            }
        }
    }

    public static function updateIsPosted($params){
        date_default_timezone_set("Asia/Kolkata");
        $movie_id = isset($params['movie_id']) ? $params['movie_id'] : '';

        if($movie_id){
            $ch = @curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://ads.tuando.me/crawler/update");
            $head[] = "Connection: keep-alive";
            $head[] = "Keep-Alive: 300";
            $head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            $head[] = "Accept-Language: en-us,en;q=0.5";

            $post = [
                'type' => 'posted',
                'movie_id' => $movie_id
            ];

            $fields_string = '';
            foreach($post as $key => $value) {
                $fields_string .= $key.'='.$value.'&';
            }

            rtrim($fields_string, '&');

            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            $page = curl_exec($ch);
            curl_close($ch);
        }
    }

    public static function syncDataElastic(){
        date_default_timezone_set("Asia/Kolkata");

        $arr_objects = array('actor', 'director', 'category', 'country', 'tags', 'movies', 'files', 'movie_actor',
            'movie_country', 'movie_category', 'movie_director', 'movie_episode', 'movie_tag');

        foreach ($arr_objects as $object){
            Model\ElasticSearch::bulk(array(
                'object_name' => $object,
                'limit' => 500
            ));

            echo "Done ". $object ."\n";
        }
    }
}