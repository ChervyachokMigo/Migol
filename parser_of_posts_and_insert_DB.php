<html>
<body>
	<?php
		//подключаем библиотеку для удобства парсинга
		require ("phpQuery-onefile.php");
		//настройки сервера
		mb_internal_encoding("UTF-8");
		mb_regex_encoding("UTF-8");
		setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');  
		date_default_timezone_set('Europe/Moscow');

		//настройки соединения к БД
		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "chervethread";

		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
		  die("Connection failed: " . $conn->connect_error);
		}

		//Сканирование папки со страницами
		$files1 = scandir("CherveThreads/");
		$files1 = array_diff($files1, [".", "..", "css", "img", "js", "makaba", "theme",""]);
		//игнорирование страниц
		$ignoreHTMLS = file_get_contents ( "ignoreHTMLS.txt" );
		$skip_pages_array = array();
		foreach(preg_split("/((\r?\n)|(\r\n?))/", $ignoreHTMLS) as $skip_Link){
			$skip_pages_array[] = trim($skip_Link);
		}
		$files1 = array_diff($files1, $skip_pages_array);

		//массив для замены месяца в числовой вид
		$date_replace_month = array(
			"Янв" => "01", "Фев" => "02", "Мар" => "03", "Апр" => "04", "Май" => "05", "Июн" => "06", "Июл" => "07", "Авг" => "08", "Сен" => "09", "Окт" => "10", "Ноя" => "11", "Дек" => "12", 
			"янв" => "01", "фев" => "02", "мар" => "03", "апр" => "04", "май" => "05", "июн" => "06","июл" => "07", "авг" => "08", "сен" => "09", "окт" => "10", "ноя" => "11", "дек" => "12"
		);

		//айди поста в базе
		$min_auto_increment = 1;
		$auto_increment = 1;

		//начало парсинга
		foreach($files1 as $findfile){
			//Задание начальных переменных цикла
			$array_all_posts = array();
			$mysql_post_content_values = array();
			$mysql_post_images_values = array();
			$mysql_post_ids_values = array();
			$mysql_post_dates_values = array();
			$sqldata_post_content = "";
			$sqldata_post_images = "";
			$sqldata_post_ids = "";
			$sqldata_post_dates = "";
			$sqldata_threads_names = "";
			$post_content = array();
			$id_post = 0;
			$id_thread = 0;
			$post_date = "";
			$page_type = 0;
			$finded=0;			

			//считываем документ
			$pathfile = "CherveThreads/".$findfile;
			$file_content = file_get_contents ( $pathfile );
			$documentQuery = phpQuery::newDocumentHTML($file_content);
			//получаем название треда
			$path_parts = pathinfo($pathfile);
			$threadname = trim(str_replace(["_Архивач", "_m2ch", "_Двач_old_1", "_Двач_old_2", "_Двач_new"], "", $path_parts['filename']  ));
			//печатаем название на экране
			echo "<div>$threadname</div>";
			//присваиваем минимальное значение для индекса постов в базе этого треда с максимального значения индекса постов предыдущего обработанного треда
			$min_auto_increment = $auto_increment;

			//определяем условием тип страницы
			//если файл - с http://arhivach.net
			if (strpos ($pathfile,'_Архивач')){
				//получить тред ид
				$id_thread = $documentQuery->find('.post:first')->attr("postid");
				//обрабатываем посты треда
				$post_content = $documentQuery->find('.post');
				foreach ($post_content as $row) {
					//получаем айди поста
					$id_post=pq($row)->attr("postid");
					if ($id_post && $id_post != $id_thread) {
						//записать сообщение в общий массив
						$array_all_posts[$id_thread][$id_post]['post_message'] = trim(pq($row)->find('.post_comment_body')->html());
						//получить дату
						$post_date = pq($row)->find('.post_time')->text();
						$post_date_tmp = date_parse_from_format("d/m/y * G:i:s+", $post_date);
						if ($post_date_tmp['error_count']<1){
							if ($post_date_tmp['second']<10) $post_date_tmp['second']="0".intval($post_date_tmp['second']);
							if ($post_date_tmp['minute']<10) $post_date_tmp['minute']="0".intval($post_date_tmp['minute']);
							if ($post_date_tmp['hour']<10) $post_date_tmp['hour']="0".intval($post_date_tmp['hour']);
							if ($post_date_tmp['day']<10) $post_date_tmp['day']="0".intval($post_date_tmp['day']);
							if ($post_date_tmp['month']<10) $post_date_tmp['month']="0".intval($post_date_tmp['month']);
							//записать дату в общий массив
							$array_all_posts[$id_thread][$id_post]['post_time'] = $post_date_tmp['month']."/".$post_date_tmp['day']."/".$post_date_tmp['year']." ".$post_date_tmp['hour'].":".$post_date_tmp['minute'].":".$post_date_tmp['second'];
						} else {
							//неправильная дата, проверка так ли это
							$date_invalid = 0;
							if ($post_date_tmp['warnings']){
								foreach ($post_date_tmp['warnings'] as $value) {
									if ($value == "The parsed date was invalid") $date_invalid = 1;
								}
							}
							//неправильная дата, парсим заного
							if ($date_invalid == 1){
								$post_date_tmp = date_parse_from_format("* d * Y G:i:s", $post_date);
								if ($post_date_tmp['second']<10) $post_date_tmp['second']="0".intval($post_date_tmp['second']);
								if ($post_date_tmp['minute']<10) $post_date_tmp['minute']="0".intval($post_date_tmp['minute']);
								if ($post_date_tmp['hour']<10) $post_date_tmp['hour']="0".intval($post_date_tmp['hour']);
								if ($post_date_tmp['day']<10) $post_date_tmp['day']="0".intval($post_date_tmp['day']);
								preg_match("/.* (\d{2}) (?P<month>[а-яА-Я]{3})/u", $post_date, $post_date_month);
								$post_date_month = $post_date_month['month'];
								$post_date_month  = strtr( $post_date_month, $date_replace_month);
								$post_date_tmp['year'] = substr($post_date_tmp['year'],2);
								//записать дату в общий массив
								$array_all_posts[$id_thread][$id_post]['post_time'] = $post_date_month."/".$post_date_tmp['day']."/".$post_date_tmp['year']." ".$post_date_tmp['hour'].":".$post_date_tmp['minute'].":".$post_date_tmp['second'];
							}
						}

						//получить прикрепленные картинки
						if (pq($row)->find('.post_image_block') != ""){
							$images_url = array();
							$images_names = array();
							//получить ссылки эскизов
						    foreach(pq($row)->find('.post_image img') as $value){
						    	$pq_tmp = pq($value);
						    	$images_url[] = $pq_tmp->attr('src');
						    }
						    //получить имена файлов
						    foreach(pq($row)->find('a.img_filename') as $value){
						    	$pq_tmp = pq($value);
						    	$images_names[] = $pq_tmp->text();
						    }
						    //записать картинки в общий массив
						    for($i=0;$i<count($images_url);$i++){
						    	$array_all_posts[$id_thread][$id_post]['post_images'][] = ['image_url'=>$images_url[$i],'image_names'=>$images_names[$i]];
						    }
						}
					}
				}
				
				$finded = 1;
			}

			//если файл - с http://m2ch.hk
			if (strpos ($pathfile,'_m2ch')){
				//получить тред ид
				$id_thread = $documentQuery->find('.thread')->attr("id");
				//получить год
				$warn_message = $documentQuery->find('.warn');
				preg_match("/Это копия, сохраненная (.*) года\./",$warn_message,$warn_date);
				$post_date_year = substr(date_parse_from_format("d M Y", $warn_date[1])['year'],2);
				//обрабатываем посты треда
				$post_content = $documentQuery->find('.reply');
				foreach ($post_content as $row) {
					//получаем айди поста
					$id_post=pq($row)->attr("id");
					if ($id_post && $id_post != $id_thread) {
						//записать сообщение в общий массив
						$array_all_posts[$id_thread][$id_post]['post_message'] = trim(pq($row)->find('.pst')->html());
						//получить дату
						$post_date = trim(pq($row)->find('.pst_bar time')->text());
						$post_date_tmp = date_parse_from_format("j *, H:i", $post_date);
						preg_match("/((\d{1,2}) (?P<month>[а-яА-Я]{3}))/u", $post_date, $post_date_month);
						$post_date_month = $post_date_month['month'];
						$post_date_month  = strtr( $post_date_month, $date_replace_month);
						if ($post_date_tmp['minute']<10) $post_date_tmp['minute']="0".intval($post_date_tmp['minute']);
						if ($post_date_tmp['day']<10) $post_date_tmp['day']="0".intval($post_date_tmp['day']);
						if ($post_date_tmp['hour']<10) $post_date_tmp['hour']="0".intval($post_date_tmp['hour']);
						//записать дату в общий массив
						$array_all_posts[$id_thread][$id_post]['post_time'] = $post_date_month."/".$post_date_tmp['day']."/".$post_date_year." ".$post_date_tmp['hour'].":".$post_date_tmp['minute'].":00";
						//получить прикрепленные картинки
						if (pq($row)->find('.thrd-thumb') != ""){
							$images_url = array();
							$images_names = array();
							//получить ссылки эскизов
						    foreach(pq($row)->find('.thrd-thumb img') as $value){
						    	$pq_tmp = pq($value);
						    	$image_src = $pq_tmp->attr('src');
						    	if (strpos($image_src, "fag/big/thumb")>0 || strpos($image_src, "img/big")>0 ) $prefix_url = "http://91.227.17.26"; else $prefix_url = "";
						    	if (strpos($image_src, "2ch.hk")>0) $image_src = str_replace("https://2ch.hk", "http://91.227.17.26",$image_src);
						    	$images_url[] = $prefix_url.$image_src;
						    	//получить имена файлов
						    	$images_names[] = basename($prefix_url.$image_src);
						    }
						    //записать все картинки в массив
						    for($i=0;$i<count($images_url);$i++){
						    	$array_all_posts[$id_thread][$id_post]['post_images'][] = ['image_url'=>$images_url[$i],'image_names'=>$images_names[$i]];
						    }
						}
					}
				}
				$finded = 2;
			}

			//если файл - с https://2ch.hk/fag/arch
			if (strpos ($pathfile,'_Двач')){
				//получаем айди треда и содержимое постов в зависимости от типа страницы
				if (strpos ($pathfile,'_old')){
					if (strpos ($pathfile,'old_1')) $page_type = 1;
					if (strpos ($pathfile,'old_2')) $page_type = 2;
					$id_thread = $documentQuery->find('.oppost-wrapper .oppost')->attr("data-num");
					$post_content = $documentQuery->find('.post-wrapper');
				}
				if (strpos ($pathfile,'_new')){
					$page_type = 3;
					$id_thread = $documentQuery->find('.thread__oppost .post_type_oppost')->attr("data-num");
					$post_content = $documentQuery->find('.thread__post');
				}
				//обрабатываем посты треда
				foreach ($post_content as $row) {
					//получаем айди поста
					$id_post=pq($row)->find("div:first")->attr("data-num");
					if ($id_post && $id_post != $id_thread) {
						//записать сообщение в общий массив и получить дату согласно типу страницы
						if ($page_type == 1 || $page_type == 2){
							$array_all_posts[$id_thread][$id_post]['post_message'] = trim(pq($row)->find('.post-message')->html());
							$post_date = pq($row)->find('.posttime')->text();							
						}
						if ($page_type == 3){
							$array_all_posts[$id_thread][$id_post]['post_message'] = trim(pq($row)->find('.post__message')->html());
							$post_date = pq($row)->find('.post__time')->text();							
						}
						//форматировать дату
						$post_date_tmp = date_parse_from_format("d/m/y * G:i:s+", $post_date);
						if ($post_date_tmp['second']<10) $post_date_tmp['second']="0".intval($post_date_tmp['second']);
						if ($post_date_tmp['minute']<10) $post_date_tmp['minute']="0".intval($post_date_tmp['minute']);
						if ($post_date_tmp['hour']<10) $post_date_tmp['hour']="0".intval($post_date_tmp['hour']);
						if ($post_date_tmp['day']<10) $post_date_tmp['day']="0".intval($post_date_tmp['day']);
						if ($post_date_tmp['month']<10) $post_date_tmp['month']="0".intval($post_date_tmp['month']);
						//записать дату в общий массив
						$array_all_posts[$id_thread][$id_post]['post_time'] = $post_date_tmp['month']."/".$post_date_tmp['day']."/".$post_date_tmp['year']." ".$post_date_tmp['hour'].":".$post_date_tmp['minute'].":".$post_date_tmp['second'];

						//получить прикрепленные картинки
						$images_url = array();
						$images_names = array();
						if ($page_type == 1 || $page_type == 2){
							if (pq($row)->find('.images') != ""){
							    foreach(pq($row)->find('figure.image') as $value){
							    	$pq_tmp = pq($value);
							    	//получить ссылки эскизов
							    	$images_url[] = $pq_tmp->find('.image-link a img')->attr('src');
							    	//получить имена файлов
							    	$images_names[] = $pq_tmp->find('.file-attr a')->attr('title');
							    }
							}
						}
						if ($page_type == 3){
							if (pq($row)->find('.post__images') != ""){
							    foreach(pq($row)->find('figure.post__image') as $value){
							    	$pq_tmp = pq($value);
							    	//получить ссылки эскизов
							    	$images_url[] = $pq_tmp->find('.post__image-link img')->attr('src');
							   		//получить имена файлов
							    	$images_names[] = $pq_tmp->find('.post__file-attr a')->attr('title');
							    }
							}
						}
						//записать картинки в общий массив
						for($i=0;$i<count($images_url);$i++){
					    	$array_all_posts[$id_thread][$id_post]['post_images'][] = ['image_url'=>$images_url[$i],'image_names'=>$images_names[$i]];
					    }
					}
				}
				$finded = 3;
			}

			//если иной файл выводим ошибку и продолжаем со следующего файла
			if ($finded ==0){
				echo "<div><b>".$pathfile." ОШИБКА ОШИБКА ОШИБКА ОШИБКА ОШИБКА ОШИБКА ОШИБКА ОШИБКА ОШИБКА ОШИБКА ОШИБКА ОШИБКА ОШИБКА </b></div>";
				file_put_contents("mysql_error_files.txt", $pathfile."\r\n", FILE_APPEND | LOCK_EX);
				file_put_contents("mysql_error_files.txt", " - Неопознаный файл\r\n", FILE_APPEND | LOCK_EX);
				continue;
			}
			
			//подготавливаем данные для записи в базу
			//айди треда
			$id_thread = intval($id_thread);
			if ($id_thread>16777214) $id_thread = 16777214;
			//обрабатываем данные с каждого поста
			foreach($array_all_posts[$id_thread] as $post_id => $post_content){
				//айди поста
				$post_id = intval($post_id);
				if ($post_id>16777214) $post_id = 16777214;
				//сообщение поста
				$post_content['post_message']="'".mysqli_real_escape_string($conn, $post_content['post_message'])."'";
				//дата поста
				$post_content['post_time']="'".date('Y-m-d G:i:s', strtotime($post_content['post_time']))."'";
				if (!$post_content['post_time']) {
					echo $post_content['post_time'];
					echo $post_content['post_message']; 
					exit("error");
				}
				
				//формируем блоки данных
				//блоки данных картинок
				if (@$post_content['post_images']){
					foreach ($post_content['post_images'] as $image_links) {
						$image_links['image_url']="'".mysqli_real_escape_string($conn, $image_links['image_url'])."'";
						$image_links['image_names']="'".mysqli_real_escape_string($conn, $image_links['image_names'])."'";
						$mysql_post_images_values[] = "($auto_increment, ".$image_links['image_url'].", ".$image_links['image_names'].")";
					}
				}
				//блоки данных текста постов
				$mysql_post_content_values[] = "($auto_increment, ".$post_content['post_message'].")";
				//блоки данных айди постов
				$mysql_post_ids_values[] = "($auto_increment, $id_thread, $post_id)";
				//блоки данных даты постов
				$mysql_post_dates_values[] = "($auto_increment, ".$post_content['post_time'].")";
				//увеличиваем виртуальный индекс поста для базы
				$auto_increment++;
			}
			//обрабатываем название треда
			$threadname = "'".mysqli_real_escape_string($conn, $threadname)."'";
			

			//запись в базу
			//если в треде есть посты
			if (count($mysql_post_content_values)>0){
				//данные названия треда для вставки в базу
				$sqldata_threads_names = "($id_thread, $threadname)";
				$sql_threads_names = "INSERT INTO threads_name(thread_id, thread_name) VALUES $sqldata_threads_names;";
				//данные текста постов для вставки в базу и запрос
				$sqldata_post_content .= implode(',', $mysql_post_content_values);
				$sql_posts_contents = "INSERT INTO posts_content(index_post, post_content) VALUES $sqldata_post_content;";
				//данные айди постов для вставки в базу и запрос
				$sqldata_post_ids .= implode(',', $mysql_post_ids_values);
				$sql_posts_ids = "INSERT INTO posts_ids(index_post, thread_id, post_id) VALUES $sqldata_post_ids;";
				//данные даты постов для вставки в базу и запрос
				$sqldata_post_dates .= implode(',', $mysql_post_dates_values);
				$sql_posts_dates = "INSERT INTO posts_data(index_post, post_date) VALUES $sqldata_post_dates;";
				
				//выполняем вставки в базу и проверяем на ошибки
				//пробуем вставить название треда в базу и если проблемы с названием треда то пишем ошибки
				if ($conn->query($sql_threads_names) === false) {
					file_put_contents("mysql_error_files.txt", $pathfile."\r\n", FILE_APPEND | LOCK_EX);
					file_put_contents("mysql_error_files.txt", " - Error: " . $conn->error . "\r\n", FILE_APPEND | LOCK_EX);
					echo "<p>Error: " . $sql_threads_names . "<br>" . $conn->error . "</p>";
					echo "<p>Skipping thread</p>";
				//если нет проблем с названием треда
				} else {
					//пробуем вставить тексты постов в базу и если проблемы с названием треда то пишем ошибки
					if ($conn->query($sql_posts_contents) === false) {
						file_put_contents("mysql_error_files.txt", $pathfile."\r\n", FILE_APPEND | LOCK_EX);
						file_put_contents("mysql_error_files.txt", " - Error: " . $conn->error . "\r\n", FILE_APPEND | LOCK_EX);
						echo "<p>Error: " . $sql_posts_contents . "<br>" . $conn->error . "</p>";
					}
					//пробуем вставить даты постов в базу и если проблемы с названием треда то пишем ошибки
					if ($conn->query($sql_posts_dates) === false) {
						file_put_contents("mysql_error_files.txt", $pathfile."\r\n", FILE_APPEND | LOCK_EX);
						file_put_contents("mysql_error_files.txt", " - Error: " . $conn->error . "\r\n", FILE_APPEND | LOCK_EX);
						echo "<p>Error: " . $sql_posts_dates . "<br>" . $conn->error . "</p>";
					}
					//пробуем вставить айди постов в базу и если проблемы с названием треда то пишем ошибки
					if ($conn->query($sql_posts_ids) === false) {
						file_put_contents("mysql_error_files.txt", $pathfile."\r\n", FILE_APPEND | LOCK_EX);
						file_put_contents("mysql_error_files.txt", " - Error: " . $conn->error . "\r\n", FILE_APPEND | LOCK_EX);
						echo "<p>Error: " . $sql_posts_ids . "<br>" . $conn->error . "</p>";
					}
					//если в треде есть картинки
					if (count($mysql_post_images_values)>0){
						//преобразуем данные и формируем запрос вставки картинок в базу
						$sqldata_post_images .= implode(',', $mysql_post_images_values);
						$sql_posts_images = "INSERT INTO posts_images(index_post, image_url, image_name) VALUES $sqldata_post_images;";
						//пробуем вставить картинки постов в базу и если проблемы с названием треда то пишем ошибки
						if ($conn->query($sql_posts_images) === false) {
							file_put_contents("mysql_error_files.txt", $pathfile."\r\n", FILE_APPEND | LOCK_EX);
							file_put_contents("mysql_error_files.txt", " - Error: " . $conn->error . "\r\n", FILE_APPEND | LOCK_EX);
							echo "<p>Error: " . $sql_posts_images . "<br>" . $conn->error . "</p>";
						}
					//если в треде отсутствуют картинки пишем ошибки
					}else{
						echo "<p>Error: no images</p>";
						file_put_contents("mysql_error_files.txt", $pathfile."\r\n", FILE_APPEND | LOCK_EX);
						file_put_contents("mysql_error_files.txt", " - Error: no images\r\n", FILE_APPEND | LOCK_EX);
					}
				}
			//если в треде отсутствуют посты пишем ошибки
			} else {
				echo "<p>Error: no posts</p>";
				file_put_contents("mysql_error_files.txt", $pathfile."\r\n", FILE_APPEND | LOCK_EX);
				file_put_contents("mysql_error_files.txt", " - Error: no posts\r\n", FILE_APPEND | LOCK_EX);
			}

			//очистка всех переменных и освобождение памяти
			$documentQuery->unloadDocument();
			unset($documentQuery);
			unset($file_content);
			unset($array_all_posts);
			unset($post_content);
			unset($mysql_post_ids_values);
			unset($mysql_post_dates_values);
			unset($mysql_post_content_values);
			unset($mysql_post_images_values);
			unset($sql_posts_contents);
			unset($sql_posts_images);
			unset($sql_threads_names);
			unset($sql_posts_ids);
			unset($sql_posts_dates);
			unset($sqldata_threads_names);
			unset($sqldata_post_content);
			unset($sqldata_post_images);
			unset($sqldata_post_ids);
			unset($sqldata_post_dates);
			gc_collect_cycles();
		}
		echo "<p>Скрипт закончен.</p>";
	?>
</body>
</html>