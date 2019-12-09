<?php
/**
 * Plugin Name: Custom API by Mike D.
 * Plugin URI: https://mikedhoore.be
 * Description: Custom endpoints for custom Table
 * Version: V0.6
 * Author: Mike Dhoore
 * Author URI: https://mikedhoore.be
 */

//Used resources:
// https://gist.github.com/ivandoric/089443880872f1b81ae74f2502e41a1c
// https://codex.wordpress.org/Class_Reference/wpdb
// https://stackoverflow.com/questions/41362277/wp-rest-api-custom-end-point-post-request
function mikeD_Contacts(){
	global $wpdb;
	$contacts = $wpdb->get_results("SELECT * FROM wp_wpdatatable_1");
	return $contacts;
}

function mikeD_ContactById($slug){
	global $wpdb;
	$contact = $wpdb->get_row("SELECT * FROM wp_wpdatatable_1 WHERE wdt_ID='".$slug["slug"]."'");
	return $contact;
}

function mikeD_ContactBySFId($slug){
	global $wpdb;
	$contact = $wpdb->get_row("SELECT * FROM wp_wpdatatable_1 WHERE salesforceid='".$slug["slug"]."'");
	return $contact;
}

function mikeD_ContactPost(WP_REST_Request $body){
	global $wpdb;
	$params  = json_decode($body->get_body());
	$existBySF = $wpdb->get_var("SELECT COUNT(wdt_ID) FROM wp_wpdatatable_1 WHERE salesforceid='".$params->salesforceid."'");
	$existById = $wpdb->get_var("SELECT COUNT(wdt_ID) FROM wp_wpdatatable_1 WHERE wdt_ID='".$params->wdt_ID."'");

	if ($existById == 0 && $existBySF == 0){
		$data =array(
			'firstname' => $params->firstname,
			'lastname' => $params->lastname,
			'email' => $params->email,
			'phone' => $params->phone,
			'title' => $params->title,
			'salesforceid' => $params->salesforceid
			);
		$wpdb->insert('wp_wpdatatable_1',$data);
		return $wpdb->insert_id;
	} elseif ($existById == 1) {
		$data =array(
			'firstname' => $params->firstname,
			'lastname' => $params->lastname,
			'email' => $params->email,
			'phone' => $params->phone,
			'title' => $params->title,
			'salesforceid' => $params->salesforceid
		);
		$where = array('wdt_ID' => $params->wdt_ID);
		if (!$wpdb->update('wp_wpdatatable_1',$data,$where)){
			http_response_code(409);
		} else {
			return "UPDATED";
		}
	} elseif ($existBySF == 1) {
		$data =array(
			'firstname' => $params->firstname,
			'lastname' => $params->lastname,
			'email' => $params->email,
			'phone' => $params->phone,
			'title' => $params->title
		);
		$where = array('salesforceid' => $params->salesforceid);
		if (!$wpdb->update('wp_wpdatatable_1',$data,$where)){
				http_response_code(409);
		}  else {
			return "UPDATED";
		}
	} else {
		http_response_code(500);
	}
}

function mikeD_ContactDelete($slug){
	global $wpdb;
	$id = array('wdt_ID' => $slug["slug"]);
	if (!$wpdb->delete('wp_wpdatatable_1',$id)){
		http_response_code(409);
	} else {
		return "DELETED";
	}
}

add_action('rest_api_init', function (){
    register_rest_route('md/v1', 'contacts',[
    	'methods' => 'GET',
		'callback' => 'mikeD_Contacts'
	]);

	register_rest_route('md/v1', 'contactById/(?P<slug>[a-zA-Z0-9-]+)',[
		'methods' => 'GET',
		'callback' => 'mikeD_ContactById'
	]);

	register_rest_route('md/v1', 'contactBySFId/(?P<slug>[a-zA-Z0-9-]+)',[
		'methods' => 'GET',
		'callback' => 'mikeD_ContactBySFId'
	]);

	register_rest_route('md/v1', 'contacts',[
		'methods' => 'POST',
		'callback' => 'mikeD_ContactPost'
	]);

	register_rest_route('md/v1', 'contacts',[
		'methods' => 'DELETE',
		'callback' => 'mikeD_ContactDelete'
	]);

});