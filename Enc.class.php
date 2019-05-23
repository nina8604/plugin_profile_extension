<?php
// логическое ядро
class Enc {

	static public function get_keys() {
		// задаем настройки при создании закрытого ключа, тип ключа OPENSSL_KEYTYPE_RSA, размер в битах будущего ключа (512 бит)
		$config = array(
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
			"private_key_bits" => 512,
		);
		// генерируем закрытый ключ и возвращаем его дескриптор
		$res = openssl_pkey_new( $config );
		// достаем значение ключа из его дескритпора
		$privKey = '';
		// Функция возвращает закрытый ключ в виде строки, строка сохраняется в переменной $privKey,
		// Первый параметр – это дескриптор ключа
		openssl_pkey_export( $res,$privKey );

		// проверка, если файл не существует, то сохраняем полученный ключ в текстовый файл
		if ( !file_exists("private.txt")){
			$fpr = fopen("private.txt","w" );
			fwrite( $fpr, $privKey );
			fclose( $fpr );
		}
		// создадим запрос для сертификата – CSR (Certificate Signing Request)
		// необходимо создать массив со следующими данными:
		$arr = array(
			"countryName" => "UA",
			"stateOrProvinceName" => "Zaporizska Oblast",
			"localityName" => "Zaporizhia",
			"organizationName" => "Organization",
			"organizationalUnitName" => "Test",
			"commonName" => "localhost",
			"emailAddress" => "nina.yaremenko86@gmail.com"
		);
		// создаем запрос CSR:
		$csr = openssl_csr_new( $arr,$privKey );
		// создаем сертификат:
		// Функция вернет дескриптор созданного сертификата.
		// NULL – обозначает, что полученный сертификат будет самостоятельно сгенерированным сертификатом
		$cert = openssl_csr_sign( $csr,NULL, $privKey,10 );
		// Функция вытащит из дескриптора $cert сертификат и сохранит его в переменной $str_cert.
		openssl_x509_export( $cert,$str_cert );
		// генерируем открытый ключ:
		// Функция возвращает дескриптор открытого ключа (на основе полученного ранее сертификата)
		$public_key = openssl_pkey_get_public( $str_cert );
		// функция возвращает массив, в котором в ячейке key, содержится открытый ключ
		$public_key_details = openssl_pkey_get_details( $public_key );

		$public_key_string = $public_key_details['key'];
		// записываем открытый ключ в файл
		if ( !file_exists("public.txt")) {
			$fpr1 = fopen("public.txt", "w");
			fwrite($fpr1, $public_key_string);
			fclose($fpr1);
		}
		// возвращаем массив с полученными ключами
		return array( 'private' => $privKey, 'public' => $public_key_string );
	}
	// метод для шифрования данных
	static public function my_enc( $str ) {

		$path = "public.txt";
		$fpr = fopen( $path,"r" );
		$pub_key = fread( $fpr,1024 );
		fclose( $fpr );

		openssl_public_encrypt( $str,$result, $pub_key );

		return $result;
	}
	// метод для дешифрования
	static public function my_dec( $str ) {
		$path = "private.txt";
		$fpr = fopen( $path,"r" );
		$pr_key = fread( $fpr,1024 );
		fclose( $fpr );

		openssl_private_decrypt( $str,$result, $pr_key );

		return $result;
	}
}
