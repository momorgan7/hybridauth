<?php
/*!
* This file is part of the HybridAuth PHP Library (hybridauth.sourceforge.net | github.com/hybridauth/hybridauth)
*
* This branch contains work in progress toward the next HybridAuth 3 release and may be unstable.
*/

namespace Hybridauth\Provider;

use Hybridauth\Exception;
use Hybridauth\Adapter\Template\OAuth1\OAuth1Template;
use Hybridauth\Entity\Twitter\Profile;
use Hybridauth\Entity\Twitter\Post;
use Hybridauth\Http\Request;


/**
* Twitter adapter extending OAuth1 Template
*
* http://hybridauth.sourceforge.net/userguide/IDProvider_info_Twitter.html
*/
class Twitter extends OAuth1Template
{
	/**
	* Internal: Initialize adapter. This method isn't intended for public consumption.
	*
	* Basically on initializers we feed defaults values to \OAuth2\Template::initialize()
	*
	* let*() methods are similar to set, but 'let' will not overwrite the value if its already set
	*/
	function initialize()
	{
		parent::initialize();

		$this->letApplicationKey( $this->getAdapterConfig( 'keys', 'key' ) );
		$this->letApplicationSecret( $this->getAdapterConfig( 'keys', 'secret' ) );

		$this->letEndpointRedirectUri( $this->getHybridauthEndpointUri() );
		$this->letEndpointBaseUri( 'https://api.twitter.com/1.1/' );
		$this->letEndpointAuthorizeUri( 'https://api.twitter.com/oauth/authenticate' );
		$this->letEndpointRequestTokenUri( 'https://api.twitter.com/oauth/request_token' );
		$this->letEndpointAccessTokenUri( 'https://api.twitter.com/oauth/access_token' );
	}

	// --------------------------------------------------------------------

	/**
	* Returns array of Post Entities
	*
	*/

	function parseTweets($raw_tweets) {
		$tweets = array();

		foreach ($raw_tweets as $tweet) {
			$tweets[] = Post::generateFromResponse($tweet, $this);
		}

		return $tweets;
	}

	// --------------------------------------------------------------------

	/**
	* Returns user profile
	*
	* Examples:
	*
	*	$data = $hybridauth->authenticate( "Twitter" )->getUserProfile();
	*/
	function getUserProfile()
	{
		$response = $this->signedRequest( 'account/verify_credentials.json' );
		$response = json_decode( $response );

		if ( ! isset( $response->id ) || isset ( $response->error ) ){
			throw new
				Exception(
					'User profile request failed: Provider returned an invalid response. ' .
					'HTTP client state: (' . $this->httpClient->getState() . ')',
					Exception::USER_PROFILE_REQUEST_FAILED,
					$this
				);
		}

		return Profile::generateFromResponse($response,$this);
	}

	// --------------------------------------------------------------------

	function toSearchQuery($term) {

		$term = str_replace(" ", "+", $term);
		$term = str_replace("#", "%23", $term);
		$term = str_replace("@", "%40", $term);

		return $term;
	}

	// --------------------------------------------------------------------

	/**
	* Return tweets filtered by search term $term.
	* Accepts search $term or array of optional params.
	*
	* See https://dev.twitter.com/docs/api/1.1/get/search/tweets for optional params.
	*/
	function searchTweets($term_or_params)
	{

		$params = array();

		if (is_array($term_or_params)) {
			$params = $term_or_params;
		} else {
			$params["q"] = $term_or_params;
		}

		// we need to properly format the $term
		$params["q"] = $this->toSearchQuery($params["q"]);

		$response = $this->signedRequest('search/tweets.json', Request::GET, $params);
		$response = json_decode($response);

		if (isset($response->error) || isset($response->errors)) {
			throw new
				Exception(
					'Search tweets request failed: Provider returned an invalid response. ' .
					'HTTP client state: (' . $this->httpClient->getState() . ')',
					Exception::SEARCH_TWEETS_REQUEST_FAILED,
					$this
				);
		}

		return $this->parseTweets($response->statuses);
	}

	// --------------------------------------------------------------------

	/**
	* Returns tweets by user $username.
	* Accepts username or array of optional params.
	*
	* See https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline for optional params.
	*/
	function getTweetsByUser($username_or_params) {

		$params = array();

		if (is_array($username_or_params)) {
			$params = $username_or_params;
		} else {
			$params["screen_name"] = $username_or_params;
		}

		$response = $this->signedRequest('statuses/user_timeline.json', Request::GET, $params);
		$response = json_decode($response);

		if (isset($response->error) || isset($response->errors)) {
			throw new
				Exception(
					'Get tweets by user request failed: Provider returned an invalid response. ' .
					'HTTP client state: (' . $this->httpClient->getState() . ')',
					Exception::USER_TWEETS_REQUEST_FAILED,
					$this
				);
		}

		return $this->parseTweets($response);
	}

	// --------------------------------------------------------------------

	/**
	* Updates user status
	*/
	function setUserStatus( &$post ) {
		$uri = 'statuses/update.json';
		if (is_string($post)) {
			$p = new Post($this);
			$p->setMessage($post);
			$post = $p;
		}
		$parameters = array();
		if(!is_null($x = $post->getMessage())) $parameters['status'] = substr($x,0,140);
		$response = $this->signedRequest($uri,Request::POST, $parameters);
		$response = json_decode( $response );
		if( !isset($response->id_str) ) return false;
		$post->setIdentifier($response->id_str);
		$post->setFrom($response->user->id_str);
		return true;
	}
}
