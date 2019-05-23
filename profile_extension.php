<?php
/*
Plugin Name: profile_extension
Description: Расширяет профиль пользователя дополнительными метаполями.
Version: 1.0
Author: NIna
*/
// подключаем файл с методами шифрования и дешифровки данных
include "Enc.class.php";
$keys = Enc::get_keys();

### дополнительные данные на странице профиля
add_action('show_user_profile', 'profile_extension_new_fields_add');
add_action('edit_user_profile', 'profile_extension_new_fields_add');

add_action('personal_options_update', 'profile_extension_new_fields_update');
add_action('edit_user_profile_update', 'profile_extension_new_fields_update');

// массив с полями, которые необходимо добавить
$new_fields=[
	'Адресс'=>'user_address',
	'Телефон'=>'phone',
	'Пол'=>'sex',
	'Семейный статус'=>'family_status',
];
// Функция добовляющая новые поля
function profile_extension_new_fields_add(){
	global $user_ID, $new_fields;
	// проверяем админ пользователь или нет
	if( current_user_can('manage_options') ){
?>
        <h3>Дополнительные данные</h3>
        <table class="form-table">
        <?php
        foreach($new_fields as $k=>$el){
            // вариант с дешифровкой данных из БД
            // $user_extention = Enc::my_dec(get_user_meta( $user_ID, $el, true  ));

            // вариант без дешифровки данных из БД
            $user_extention=get_user_meta( $user_ID, $el, true );
        ?>
            <tr>
                <th><label for="<?php echo $el ?>"><?php echo $k ?></label></th>
                <td>
                    <input type="text" name="<?php echo $el ?>" value="<?php echo $user_extention ?>"><br>
                </td>
            </tr>
        <?php
        }
        ?>
        </table>
	<?php
	}
}

// обновление данных в БД
function profile_extension_new_fields_update(){
	global $user_ID, $new_fields;
	// проверяем админ пользователь или нет
	if( current_user_can('manage_options') ) {
		foreach ($new_fields as $field) {
			// вариант с шифрованием данных в БД
			//$user_field=Enc::my_enc($_POST[$field]);

			// вариант с без шифрованием данных в БД
			$user_field = ($_POST[$field]);

			update_user_meta($user_ID, $field, $user_field);
		}
	}
}

// отображение списка пользователей
function show_registered_users(){
	$args = array(
		'orderby' => 'user_nicename',
		 'order' => 'ASC',
	);
	$registeredusers = '<ul class="registered-user">';
	$users=get_users($args);
	foreach($users as $user){
		$registeredusers .= '<li><a href="wp-admin/user-edit.php?user_id='.$user->ID.'&wp_http_referer=%2Fwp-admin%2Fusers.php">'.$user->user_nicename.'</a></li>';
    }
	$registeredusers .= '</ul>';
	return $registeredusers;
}
// создание шорткода
add_shortcode('wpb_newusers', 'show_registered_users');

