<?php

namespace App\Http\Controllers;

use App\comment;
use App\commentMention;
use Facebook\Facebook;
use Illuminate\Http\Request;

class facebookController extends Controller
{
    //
    protected $info;
    protected $redirectUrl;
    protected $accessToken;
    protected $endpointBase;
    protected $pagesInfo;
    protected $instagramPageInfo;

    public function __construct()
    {
        $appID = env('Facebook_APP_ID');
        $secret = env('Facebook_Secret_ID');
        $this->redirectUrl = 'https://royakiki.drrejeem.ir/getToken';
        $callback = env('Facebook_CallBack');
        $this->endpointBase = 'https://graph.facebook.com/v6.0/';

        $this->info = array(
            'app_id' => $appID,
            'app_secret' => $secret,
            'default_graph_version' => 'v3.2',
            'persistent_data_handler' => 'session'
        );
    }

    public function index()
    {
        return view('index');
    }

    public function getToken()
    {
        session_start();
        $facebook = new Facebook($this->info);
        // helper
        $helper = $facebook->getRedirectLoginHelper();
        $oAuth2Client = $facebook->getOAuth2Client();
        if (isset($_GET['code'])) { // get access token
            try {
                $accessToken = $helper->getAccessToken();
            } catch (Facebook\Exceptions\FacebookResponseException $e) { // graph error
                echo 'Graph returned an error ' . $e->getMessage;
            } catch (Facebook\Exceptions\FacebookSDKException $e) { // validation error
                echo 'Facebook SDK returned an error ' . $e->getMessage;
            }

            if (!$accessToken->isLongLived()) { // exchange short for long
                try {
                    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    echo 'Error getting long lived access token ' . $e->getMessage();
                }
            }

            $accessToken = (string)$accessToken;
            $this->accessToken = $accessToken;
            session(['token' => $accessToken]);
            session()->flash('message', 'New token generated.');
            session()->flash('type', 'success');
            return redirect(route('home'));
        } else { // display login url
            $permissions = ['public_profile', 'instagram_basic', 'pages_show_list', 'instagram_manage_insights', 'instagram_manage_comments', 'manage_pages'];
            $loginUrl = $helper->getLoginUrl($this->redirectUrl, $permissions);

            echo '<a href="' . $loginUrl . '">
            Login With Facebook
        </a>';
        }
    }

    public function getFacebookPageInfo()
    {
        $endpointFormat = $this->endpointBase . 'me/accounts?access_token={access-token}';
        $pagesEndpoint = $this->endpointBase . 'me/accounts';
        $pagesParams = array(
            'access_token' => session()->get('token')
        );
        $pagesEndpoint .= '?' . http_build_query($pagesParams);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pagesEndpoint);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $responseArray = json_decode($response, true);
        unset($responseArray['data'][0]['access_token']);
        $this->pagesInfo['facebook'] =
            [
                'name' => $responseArray['data'][0]['name'],
                'id' => $responseArray['data'][0]['id']
            ];
    }

    public function getInstagramPageInfo()
    {
        $endpointFormat = $this->endpointBase . '{page-id}?fields=instagram_business_account&access_token={access-token}';
        $instagramAccountEndpoint = $this->endpointBase . $this->pagesInfo['facebook']['id'];

        // endpoint params
        $igParams = array(
            'fields' => 'instagram_business_account',
            'access_token' => session()->get('token')
        );

        // add params to endpoint
        $instagramAccountEndpoint .= '?' . http_build_query($igParams);

        // setup curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $instagramAccountEndpoint);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // make call and get response
        $response = curl_exec($ch);
        curl_close($ch);
        $responseArray = json_decode($response, true);
        $this->pagesInfo['instagram'] =
            [
                'id' => $responseArray['instagram_business_account']['id']
            ];
    }

    public function getInstagramPageInfoMain()
    {
        $endpointFormat = $this->endpointBase . '{ig-user-id}?fields=business_discovery.username({ig-username}){username,website,name,ig_id,id,profile_picture_url,biography,follows_count,followers_count,media_count,media{caption,like_count,comments_count,media_url,permalink,media_type}}&access_token={access-token}';
        $endpoint = $this->endpointBase . $this->pagesInfo['instagram']['id'];

        // username
        $username = 'mezon_royakiki';

        // endpoint params
        $igParams = array(
            'fields' => 'business_discovery.username(' . $username . '){username,website,name,ig_id,id,profile_picture_url,biography,follows_count,followers_count,media_count,media{caption,like_count,comments_count,media_url,permalink,media_type}}',
            'access_token' => session()->get('token')
        );

        // add params to endpoint
        $endpoint .= '?' . http_build_query($igParams);

        // setup curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // make call and get response
        $response = curl_exec($ch);
        curl_close($ch);
        $responseArray = json_decode($response, true);
        $this->instagramPageInfo = $responseArray;
    }

    public function pageInfo()
    {
        $this->getFacebookPageInfo();
        $this->getInstagramPageInfo();
        $this->getInstagramPageInfoMain();
        session(['pagesInfo', $this->pagesInfo]);
        session(['instagramPageInfo', $this->instagramPageInfo]);
        $info = $this->pagesInfo;
        $pageInfo = $this->instagramPageInfo;
        return view('pageInfo', compact('info', 'pageInfo'));
    }

    public function postList()
    {

    }

    public function makeApiCall($endpoint, $type, $params)
    {
        $ch = curl_init();

        if ('POST' == $type) {
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ('GET' == $type) {
            curl_setopt($ch, CURLOPT_URL, $endpoint . '?' . http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getComment(Request $request)
    {


        // endpoint formats
        $commentsEndpointFormat = $this->endpointBase . '{ig-media-id}/comments?fields=like_count,replies,username,text';
        $repliesEndpointFormat = $this->endpointBase . '{ig-comment-id}/replies?fields=username,text,like_count';
        $postCommentEndpointFormat = $this->endpointBase . '{ig-media-id}/comments?message={message}';
        $postReplyEndpointFormat = $this->endpointBase . '{ig-comment-id}/replies?message={message}';

        // post comment to IG
        // $postCommentEndpoint = ENDPOINT_BASE . $mediaObject['id'] . '/comments';
        // $postCommentIgParams = array(
        // 	'message' => 'Commenting from IG Graph API!! :)',
        // 	'access_token' => $accessToken
        // );
        // $postCommentResponseArray = makeApiCall( $postCommentEndpoint, 'POST', $postCommentIgParams );
        // echo '<pre>';
        // print_r($postCommentResponseArray);
        // die();

        // post reply to comment
        // $commentId = '17982899548288082';
        // $postReplyEndpoint = ENDPOINT_BASE . $commentId . '/replies';
        // $postReplyIgParams = array(
        // 	'message' => 'Reply coming from IG Graph API!! :)',
        // 	'access_token' => $accessToken
        // );
        // $postReplyResponseArray = makeApiCall( $postReplyEndpoint, 'POST', $postReplyIgParams );
        // echo '<pre>';
        // print_r($postReplyResponseArray);
        // die();


        // get comments from IG
        $commentsEndpoint = $this->endpointBase . $request['id'] . '/comments';
        $igParams = array(
            'fields' => 'like_count,replies,username,text',
            'access_token' => session()->get('token')
        );
        $responseArray = $this->makeApiCall($commentsEndpoint, 'GET', $igParams);
        foreach ($responseArray['data'] as $datum) {
            $check = comment::where(
                [
                    ['postID', '=', $request['id']],
                    ['commentID', '=', $datum['id']],
                ]
            )->first();
            if (!$check) {
                comment::create(
                    [
                        'postID' => $request['id'],
                        'commentID' => $datum['id'],
                        'username' => $datum['username'],
                        'text' => $datum['text'],
                        'likeCount' => $datum['like_count'],
                    ]
                );
                $text = explode('@', $datum['text']);
                if (sizeof($text) >= 1) {
                    foreach ($text as $item) {
                        commentMention::create(
                            [
                                'commentID' => $datum['id'],
                                'text' => trim($item),
                            ]
                        );
                    }
                }
            }
        }
        dd($responseArray);
    }
}
