<div class="upload-plugin">
	<p class="install-help">
        ~ Upload Clubs CSV file to import Clubs Only
				  <br>
				~ Upload Afga Members CSV file to import members and add them as friends to Clubs
    </p>
	<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="" id="afga-import" >
		<input type="file" id="afga-members" name="afga-members">
		<input type="submit" name="import-now" class="button" value="Import Now" disabled="">	
    </form>
</div>
<?php 
include("../wp-blog-header.php");

//$temp = get_user_meta(3659);

//print_r($temp);

//foreach ($temp as $key => $value) {
	# code...

	//echo get_user_meta(3659,$key,true);
	//delete_user_meta(3659, $key);
//}

function create_profile($fields,$level){


	//$club = '';

	//echo 'i am here';
	//die;

	if($level == 1):

		$birth_year = date('Y',strtotime($fields['date_of_birth']));

		$current_year = date('Y');
		
		$age_difference = $current_year - $birth_year;

	  if($age_difference > 16):

			$first_name = $fields['first_name'];
			$last_name = $fields['last_name'];
			$display_name = $first_name .' '.$last_name;
	
			if(!email_exists($fields['email'])):
				$user_email = $fields['email'];
			else:
				$user_email =  'test_'.$fields['id'].'@outdooradventurists.com';
			endif;
			//$user_email = $fields['email'];
			$cell_phone = $fields['phone_nbr'];
			$password = $fields['id'];
			$membership_id = 1 ;
			$club =	strtolower(trim($fields['club']));
	
		endif;
	
	else:

		$contact_name = explode(" ",$fields['club_name']);

		$first_name = '';
		$last_name = '';
		$display_name = $fields['club_name'];
		$user_email = $fields['contact_email'];
		$cell_phone = $fields['contact_phone'];
		$password = $fields['zone'];
		$membership_id = 2 ;

	endif;



	

	$nicename = $display_name;

	$nicename = sanitize_title($nicename);
	$nicename_original = $nicename;
	$i = 2;

	while (username_exists($nicename) !== false) {
		$nicename = $nicename_original.'-'.($i++);
	}

	$args['user_login'] = $nicename;
	$args['display_name'] = $display_name;
	$args['nickname'] = $display_name;
	$args['user_email'] = $user_email;
	$args['first_name'] = $first_name;
	$args['last_name'] = $last_name;
	$args['role'] = 'contributor';

	//print_r($args);


	if($level == 1):

		if($club == 'direct'){

			$club_user = 'alberta-fish-and-game-association';
		}
		else{

			$club_user = str_replace(' ','-',$club);
		}

	else:

		$club_user = 'alberta-fish-and-game-association';

	endif;

   $user_data = get_user_by('login',$club_user);

	//if($club=='' && !empty(get_user_by('login', 'alberta-fish-and-game-association'))){



	//}

	$user_id = wp_insert_user($args);
	if (!is_wp_error($user_id)) {

	//if($level == 1):	

		friends_add_friend($user_data->ID,$user_id,true);

		$settings_keys = array(
			'notification_friends_friendship_request'  	=> 'no',
			'notification_friends_friendship_accepted' 	=> 'no',
			'notification_groups_invite'               	=> 'no',
			'notification_groups_group_updated'					=> 'no',
			'notification_groups_admin_promotion'				=> 'no',
			'notification_groups_membership_request'		=> 'no',
			'notification_membership_request_completed' => 'no',
			'notification_groups_calendar_event'				=> 'no',
			'notification_activity_new_mention'					=> 'no',
			'notification_activity_new_reply'						=> 'no',
			'notification_messages_new_message'					=> 'no',
			'notification_starts_following'							=> 'no',
			
	);

	foreach( $settings_keys as $setting => $preference ) {

			bp_update_user_meta( $user_id,  $setting, $preference );
	}

	//endif;

	bp_update_user_last_activity( $user_id, bp_core_current_time() );
	update_user_meta( $user_id,'pmpro_email_confirmation_key', 'validated');


	bbp_set_user_role($user_id, 'bbp_participant');
	wp_set_password($password, $user_id);


	xprofile_set_field_data('Cell Phone', $user_id, $cell_phone);
	xprofile_set_field_data('Alternative Email', $user_id, $user_email);
	xprofile_set_field_data('Club Contact Person - Cell Phone Nbr', $user_id, $cell_phone);
	xprofile_set_field_data('Club Main Email Address', $user_id, $user_email);
		
	$mem_cell_id = xprofile_get_field_id_from_name('Cell Phone');
	$mem_cell_visibility = xprofile_get_field_visibility_level($mem_cell_id,$user_id);

	xprofile_set_field_visibility_level($mem_cell_id , $user_id, $mem_cell_visibility);

	$mem_email_id = xprofile_get_field_id_from_name('Alternative Email');
	$mem_email_visibility = xprofile_get_field_visibility_level($mem_email_id,$user_id);

	xprofile_set_field_visibility_level($mem_email_id , $user_id, $mem_email_visibility);

	$club_cell_id = xprofile_get_field_id_from_name('Club Contact Person - Cell Phone Nbr');
	$club_cell_visibility = xprofile_get_field_visibility_level($club_cell_id,$user_id);

	xprofile_set_field_visibility_level($club_cell_id , $user_id, $club_cell_visibility);

	$club_email_id = xprofile_get_field_id_from_name('Club Main Email Address');
	$club_email_visibility = xprofile_get_field_visibility_level($club_email_id,$user_id);

	xprofile_set_field_visibility_level($club_email_id , $user_id, $club_email_visibility);
	
	global $wpdb;
	$wpdb->insert('65_pmpro_memberships_users',array('user_id'=>$user_id,'membership_id'=>$membership_id,
	'status'=>'active'),array('%s','%s','%s'));

	}

}


if(!empty($_FILES)):

	$members = $_FILES['afga-members']['tmp_name'];

	//print_r($_FILES);

	$array = $fields = array(); 
	$i = 0;
	$handle = @fopen($members, "r");
	if ($handle) {
		while (($row = fgetcsv($handle, 4096)) !== false) {
			if (empty($fields)) {
				$fields = $row;
			continue;
		}
		foreach ($row as $k=>$value) {
			$array[$i][strtolower(   str_replace(' ','_',$fields[$k] )  ) ] = $value;
		}
		$i++;
	}
	if (!feof($handle)) {
		echo "Error: unexpected fgets() fail\n";
	}
		fclose($handle);
	}
	
 $club_count = $mem_count = 0;

	foreach ($array as $value) {

		print_r($value);
	
		if(!empty($value['club_name'])):

			if(!username_exists(str_replace(' ','-',strtolower($value['club_name'])))):
				echo create_profile($value,2);
			endif;


		elseif(!empty($value['first_name'])):

				echo create_profile($value,1);
	
		endif;

	}

	die;

endif;