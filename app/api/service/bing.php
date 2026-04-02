<?php
namespace app\api\service;

 /**
 * 获取bing每日壁纸
 */
class Bing {
	
	private $bingApiXml;
  	private $bingApiJson;

	function __construct()
	{
        /**
         * API https://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1&mkt=zh-CN
         * n 表示返回图片的数量，最多8张
         * idx 起始时间 0表示今日 -1表示昨日
         * format 返回数据格式
         */

               		
        // 返回xml格式
		$this->bingApiXml = "https://cn.bing.com/HPImageArchive.aspx?idx=0&n=1";

		// 返回json格式
		$this->bingApiJson = "https://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1";
	}

	/**
	 * xml格式数据
	 * @return [type] [description]
	 */
	public function getDataXml()
	{
		$url = $this->bingApiXml;
		$data = $this->httpGet($url);
		return $data;
	}

	/**
	 * json格式数据
	 * @return [type] [description]
	 */
	public function getDataJson()
	{
		$url = $this->bingApiJson;
		$data = $this->httpGet($url);
		return $data;
	}

	private function httpGet($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);

		$res = curl_exec($curl);
		curl_close($curl);

		return $res;
	}
}

// $BingPic = new GetBingPic();
// $data = $BingPic->getDataJson();
// var_dump($data);
