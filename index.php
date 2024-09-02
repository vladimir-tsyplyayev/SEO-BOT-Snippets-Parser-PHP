<?php

set_time_limit(0);
$useragents = file('utilites/useragents.txt');
$lang = file_get_contents('settings/language.txt');
$se = file_get_contents('settings/se.txt');
$treads = file_get_contents('settings/treads.txt');
$limit = file_get_contents('settings/limit.txt');

// SNIPPET PARSER --------------------------------------------------------------
//////////////////////////////////
// FUNCTIONS >>>>>>>>>>>>>>>>>>>>>----------------------------------------------

function multi_take_html($url, $proxy)
{
	global $treads;
	global $timeout;
	global $useragents;

	$result = array();

	$multi_handle = null;
	$curl_handles = array();
	$options = array();

	$multi_handle = curl_multi_init();

	$q=0;
	$prs=0;
	for($i=0;$i<=$treads;$i++){
	  if(isset($url[$i])){
		if(strlen(trim($url[$i]))>0){
			$fp = fopen("utilites/cookie/cookie".$i.".txt", "w");
			fclose($fp);
			$reffer =  parse_url($url[$i]);
         	$curl_handles[$q] = curl_init($url[$i]);
         	/*CURLOPT_HEADER => 1,*/

         	if(count($proxy)==0){
         		$options[$q] = array (
					CURLOPT_TIMEOUT => 30,
					CURLOPT_COOKIEFILE => "utilites/cookie/cookie".$i.".txt",
					CURLOPT_COOKIEJAR => "utilites/cookie/cookie".$i.".txt",
					CURLOPT_FOLLOWLOCATION => TRUE,
	     			CURLOPT_RETURNTRANSFER => TRUE,
	     			CURLOPT_REFERER => $reffer['host'],
	     			CURLOPT_SSL_VERIFYPEER => 0,
	     			CURLOPT_HTTPHEADER => array("Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/x-icq, */*","Accept-Language: en-US;en;q=0.5","US-CPU: x86","User-Agent: ".trim($useragents[rand(0,(count($useragents)-1))]),"Connection: Keep-Alive")
				);

         	}else{

				$options[$q] = array (
					CURLOPT_TIMEOUT => 30,
					CURLOPT_COOKIEFILE => "utilites/cookie/cookie".$i.".txt",
					CURLOPT_COOKIEJAR => "utilites/cookie/cookie".$i.".txt",
					CURLOPT_FOLLOWLOCATION => TRUE,
	     			CURLOPT_RETURNTRANSFER => TRUE,
	     			CURLOPT_REFERER => $reffer['host'],
	     			CURLOPT_SSL_VERIFYPEER => 0,
	     			CURLOPT_HTTPHEADER => array("Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/x-icq, */*","Accept-Language: en-US;en;q=0.5","US-CPU: x86","User-Agent: ".trim($useragents[rand(0,(count($useragents)-1))]),"Connection: Keep-Alive"),
	     			CURLOPT_PROXY => trim($proxy[$i])
				);
			}

    	curl_setopt_array($curl_handles[$q], $options[$q]);
	    curl_multi_add_handle($multi_handle, $curl_handles[$q]);
	    $q++;
    	}
      }
	}

	do { curl_multi_exec($multi_handle, $running);
	} while ($running > 0);

	for ($n = 0; $n < count($curl_handles); $n++){
	    $result[$n] = curl_multi_getcontent($curl_handles[$n]);
	}

	for($i=0;$i<count($curl_handles);$i++){
	    curl_multi_remove_handle($multi_handle,$curl_handles[$i]);
	    curl_close($curl_handles[$i]);
	}
	curl_multi_close($multi_handle);

	return $result;
}

// END FUNCTIONS >>>>>>>>>>>>>>>>>>>>>------------------------------------------
//////////////////////////////////////

if(file_exists('utilites/read_counter.txt')){
	$s = trim(file_get_contents('utilites/read_counter.txt'))*1;
}else{
	$s = 0;
}
$keys_file = 'settings/keywords.txt';
if(!file_exists('utilites/temp_keywords.txt') || $s==0){
	$fil = fopen('utilites/temp_keywords.txt',"w");
		$file = file($keys_file);
		foreach ($file as $keyword){
			if(!(preg_match('/\/u/i',$keyword) || preg_match('/\\u/i',$keyword)) ){
	    		fputs ($fil, trim(iconv('UTF-8', 'windows-1251',$keyword)).'||'.trim(iconv('UTF-8', 'windows-1251',$file[0])).'
');
			}
		}
	fclose($fil);
}
$file = file("utilites/temp_keywords.txt");

if(count($file)>0){

	$proxy = file("settings/proxy.txt");
	if(!file_exists('utilites/temp_proxy.txt') || $s==0){
		copy('settings/proxy.txt','utilites/temp_proxy.txt');
	}else{
		$proxy = file("utilites/temp_proxy.txt");
	}


	$se_parts = explode('.',$se);
	if(strcmp('aol',$se_parts[0])==0){
		$url2google = 'http://search.'.$se.'/aol/search?s_it=topsearchbox.search&v_t=na&lr=lang_'.$lang.'&q=';
	}else{
		$url2google = 'https://www.'.$se.'/search?num=100&hl='.$lang.'&q=';
	}




	$url = array();
    $proxy1 = array();
    for($i=0;$i<$treads;$i++){
    	if(isset($file[$i])){
    		$parts = explode('||',$file[$i]);
			if(strlen(trim($parts[0]))>0 && !file_exists('snippets/'.str_replace('/',' ',str_replace('*',' ',trim($parts[0]))).'.txt')){
				$url[$i]=$url2google.urlencode(trim($parts[0]));
			}
		}
    }
    for($i=0;$i<$treads;$i++){
		$proxy1[$i]=trim($proxy[$i]);
    }

//print_r($url);

// запускаем мультикурл ********************************************************
	$result = multi_take_html($url, $proxy1);
//******************************************************************************
// анализ результатов отдельно каждого потока **********************************
 //print_r($result);


    $captcha = 0;

    $fil = fopen("utilites/temp_keywords.txt","w");
    $fil2 = fopen('utilites/temp_proxy.txt',"w");
    foreach($file as $i => $v){
    	if($i<$treads){
    		if(strlen(trim($result[$i]))>0){
    			/*$filsug = fopen('log/'.$i.'.txt',"a");
					fputs ($filsug, $result[$i]);
			        fclose($filsug);*/


		    	if(!preg_match('/resultStats">/i',$result[$i])){
		        	fputs ($fil, trim($file[$i]).'
');
			        $captcha = 1;
		        }else{

			        preg_match_all('#<span class="st">(.*?)</span>#is',$result[$i],$snip);

		        	if(count($snip[1])>0){
						$parts = explode('||',$file[$i]);
			        	
			        	$filsug = fopen('snippets/'.str_replace('/',' ',str_replace('*',' ',(trim($parts[0])))).'.txt',"a");
			        	/*fputs ($filsug, trim($file[$i]).'
');*/
			        	$vs2 = '';
			        	$pred=0;
			        	foreach($snip[1] as $vs){

			        		$vs2 .= str_replace(',.','.',str_replace('[]','',
			        		str_replace('!!!','',
			        			str_replace('..','.',iconv('UTF-8', 'windows-1251',

			        		str_replace(array('+7', '+3', '(', ')', 'авг.', 'июля', 'июня', 'окт.', 'дек.', 'сент.', 'марта', '2014 г.',
			        		 '2012 г.', '2013 г.', 'http://', '|'),'',
			        		preg_replace("/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/",'',
			        		preg_replace('/\d{3}([().-\s[\]]*)\d{3}([().-\s[\]]*)\d{4}/','',
			        		preg_replace('/\d{1,2}\s.*?\-\./is','',

			        			

			        		

			        			

			        		str_replace('..','.',trim( ucfirst( str_replace(array("\r\n", "\r", "\n"),'',str_replace('  ',' ',str_replace('
',' ',str_replace('&middot;'," ",str_replace('&nbsp;'," ",str_replace(' .',".",str_replace(' .',".",str_replace('&#39;',"'", str_replace('<span class="pre-desc">',"", str_replace('</span>',"", str_replace('..',' ',str_replace('...',' ', str_replace('  ',' ', str_replace('  ',' ', str_replace('  ',' ', str_replace('  ',' ', str_replace(' .','.', str_replace(', .','.', str_replace('<b>',' ', str_replace('</b>',' ',
	strip_tags($vs))))))))))))))))))))))))))).'. '))))));
$pred++;
if(rand(0,10)>5 && $pred>2){$vs2 .='

';
$pred=0;
}
						}
						preg_match_all('/\d{1,2}\s.*?\-\./is',$vs2,$vs2_tmp);
						foreach ($vs2_tmp[0] as $vs2_tmp_v) {
							$vs2 = str_replace($vs2_tmp_v,'',$vs2);
						}
						preg_match_all('/\d{1,2}\:\d{1,2}/is',$vs2,$vs2_tmp);
						foreach ($vs2_tmp[0] as $vs2_tmp_v) {
							$vs2 = str_replace($vs2_tmp_v,'',$vs2);
						}
						preg_match_all('/\d{1,2}\s.{3,6}?\s\d{4}/is',$vs2,$vs2_tmp);
						foreach ($vs2_tmp[0] as $vs2_tmp_v) {
							$vs2 = str_replace($vs2_tmp_v,'',$vs2);
						}
						preg_match_all('/\d{1,2}\.\d{1,2}?\.\d{2,4}/is',$vs2,$vs2_tmp);
						foreach ($vs2_tmp[0] as $vs2_tmp_v) {
							$vs2 = str_replace($vs2_tmp_v,'',$vs2);
						}
						preg_match_all('/\d{1,2}\;|\d{1,2}\./is',$vs2,$vs2_tmp);
						foreach ($vs2_tmp[0] as $vs2_tmp_v) {
							$vs2 = str_replace($vs2_tmp_v,'',$vs2);
						}


						
													fputs ($filsug, substr(preg_replace('/\d{1,2}\.\d{1,2}\.\d{4} \-\./i','',  $vs2), 0, $limit));

		        		fclose($filsug);

						if(strlen(trim($proxy[$i]))>0){
		        			fputs ($fil2, trim($proxy[$i]).'
');
						}
					}else{
						if(strlen(trim($file[$i]))>0){
							fputs ($fil, trim($file[$i]).'
');
						}
					}
		        }
	        }else{
	        	if(strlen(trim($file[$i]))>0){
		        	fputs ($fil, trim($file[$i]).'
');
				}
	        }
	    }else{
	    	if(strlen(trim($file[$i]))>0){
		        fputs ($fil, trim($file[$i]).'
');
			}
			if(isset($proxy[$i])){
	        	if(strlen(trim($proxy[$i]))>0){
		    		fputs ($fil2, trim($proxy[$i]).'
');
				}
			}
	    }
    }
    fclose($fil);
    for($j=$i;$j<=count($proxy);$j++){
    	if(isset($proxy[$j])){
    		if(strlen(trim($proxy[$j]))>0){
				fputs ($fil2, trim($proxy[$j]).'
');
			}
		}
    }
    fclose($fil2);

	function shuffle_prixy($filename){
		$proxy = file($filename);
		shuffle($proxy);
		$fil = fopen($filename,"w");
		foreach ($proxy as $v) {
			if(isset($v)){
    			if(strlen(trim($v))>0){
					fputs ($fil, trim($v).'
');
				}
			}
		}
		fclose($fil);
	}
	shuffle_prixy('utilites/temp_keywords.txt');


    if(count($proxy)<$treads){
		copy('utilites/proxy.txt','utilites/temp_proxy.txt');
		print('All proxy died :-(<br>Reload proxylist.');
	}

	$s++;
	$filec = fopen('utilites/read_counter.txt', 'w');
	fputs ($filec, $s);
	fclose($filec);


	print('<h1>Snippets parsing</h1><br>Treads: '.$treads.'<br>Keywords in queue: '.count($file).'<br>Cycyle #'.$s.'<br>Proxy: '.count($proxy).'
	<script>
	window.location.replace("index.php");
	</script>');
}else{
	print('<h1>Snippets parsing Complete!</h1>');
	unlink('utilites/read_counter.txt');
	unlink('utilites/temp_keywords.txt');
}

?>
