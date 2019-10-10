<?php

/**
 * Class LP_REST_Users_Controller
 *
 * @since 4.x.x
 */
class LP_REST_Users_Controller extends LP_Abstract_REST_Controller {
	/**
	 * @var LP_User
	 */
	protected $user = null;

	/**
	 * @var LP_Course
	 */
	protected $course = null;

	/**
	 * @var LP_Course_Item|LP_Quiz|LP_Lesson
	 */
	protected $item = null;

	/**
	 * @var LP_User_Item_Course
	 */
	protected $userCourse = null;

	/**
	 * @var LP_User_Item|LP_User_Item_Quiz
	 */
	protected $userItem = null;

	public function __construct() {
		$this->namespace = 'lp/v1';
		$this->rest_base = 'users';
		parent::__construct();

		add_filter( 'rest_pre_dispatch', array( $this, 'rest_pre_dispatch' ), 10, 3 );
	}

	/**
	 * Init data prepares for callbacks of rest
	 *
	 * @param                 $null
	 * @param WP_REST_Server  $server
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function rest_pre_dispatch( $null, $server, $request ) {
		$user_id   = get_current_user_id();
		$item_id   = $request['item_id'];
		$course_id = $request['course_id'];

		$this->user   = learn_press_get_user( $user_id );
		$this->course = learn_press_get_course( $course_id );

		if ( $this->course ) {
			$this->item = $this->course->get_item( $item_id );
		}

		if ( is_user_logged_in() ) {
			$this->userCourse = $this->user->get_course_data( $course_id );

			if ( $this->userCourse ) {
				$this->userItem = $this->userCourse->get_item( $item_id );
			}
		}

		return $null;
	}

	/**
	 * Register rest routes.
	 */
	public function register_routes() {
		$this->routes = array(
			'' => array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			),

			'(?P<key>[\w]+)' => array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the resource.', 'learnpress' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'enroll_course' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			),

			'start-quiz' => array(
				array(
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => array( $this, 'start_quiz' ),
					//'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'     => $this->get_item_endpoint_args()
				),
			),

			'submit-quiz' => array(
				array(
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => array( $this, 'submit_quiz' ),
					//'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'     => $this->get_item_endpoint_args()
				),
			),

			'hint-answer' => array(
				array(
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => array( $this, 'hint_answer' ),
					//'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'     => $this->get_item_endpoint_args()
				),
			),

			'check-answer' => array(
				array(
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => array( $this, 'check_answer' ),
					//'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'     => $this->get_item_endpoint_args()
				),
			)
		);

		parent::register_routes();
	}

	/**
	 * Get args for user item endpoints.
	 *
	 * @return array
	 */
	public function get_item_endpoint_args() {
		return array(
//			'user_id'   => array(
//				'description'       => __( 'The ID of user object.', 'learnpress' ),
//				'type'              => 'int',
//				'validate_callback' => array( $this, 'validate_arg' ),
//				//'required'          => true
//			),
			'item_id'   => array(
				'description'       => __( 'The ID of course item object.', 'learnpress' ),
				'type'              => 'int',
				'validate_callback' => array( $this, 'validate_arg' ),
				'required'          => true
			),
			'course_id' => array(
				'description'       => __( 'The ID of course object.', 'learnpress' ),
				'type'              => 'int',
				'validate_callback' => array( $this, 'validate_arg' ),
				'required'          => true
			)
		);
	}

	/**
	 * Validation callback to verify rest args.
	 *
	 * @param mixed           $value
	 * @param WP_REST_Request $request
	 * @param string          $param
	 *
	 * @return bool|WP_Error
	 */
	public function validate_arg( $value, $request, $param ) {
		$attributes = $request->get_attributes();

		if ( ! isset( $attributes['args'][ $param ] ) ) {
			return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%s was not registered as a request argument.', 'learnpress' ), $param ), array( 'status' => 400 ) );
		}

		return true;
	}

	/**
	 * Sanitize callback.
	 *
	 * @param mixed           $value
	 * @param WP_REST_Request $request
	 * @param string          $param
	 *
	 * @return mixed
	 */
	public function sanitize_arg( $value, $request, $param ) {
		switch ( $param ) {
			case 'user_id':
			case 'item_id':
			case 'course_id':
				return absint( $value );
		}

		return $value;
	}

	public function check_admin_permission() {
		return LP_REST_Authentication::check_admin_permission();
	}

	/**
	 * Enroll an user to a course.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function enroll_course( $request ) {
		$response = array(
			$_REQUEST
		);

		return rest_ensure_response( $response );
	}

	/**
	 * User starts a quiz.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function start_quiz( $request ) {

		$user_id   = get_current_user_id();
		$item_id   = $request['item_id'];
		$course_id = $request['course_id'];
		$user      = learn_press_get_user( $user_id );

		if ( $user->has_started_quiz( $item_id, $course_id ) ) {
			$result = $user->retake_quiz( $item_id, $course_id, true );
		} else {
			$result = $user->start_quiz( $item_id, $course_id, true );
		}

		$success = ! is_wp_error( $result );

		$response = array(
			'success' => $success,
			'message' => ! $success ? $result->get_error_message() : __( 'Success!', 'learnpress' )
		);

		if ( $success ) {
			$response['results'] = array();
		}

		return rest_ensure_response( $response );
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function submit_quiz( $request ) {
		$user_id     = get_current_user_id();
		$item_id     = $request['item_id'];
		$course_id   = $request['course_id'];
		$answered    = $request['answered'];
		$user        = learn_press_get_user( $user_id );
		$user_course = $user->get_course_data( $course_id );
		$result      = false;
		$user_quiz   = false;

		if ( $user_course ) {
			$user_quiz = $user_course->get_item( $item_id );

			if ( $user_quiz ) {
				$user_quiz->add_question_answer( $answered );
				$user_quiz->update();

				$result = $user_quiz->get_results( '', true );
			}
		}

		$finished = $user->finish_quiz( $item_id, $course_id, true );
		$success  = ! is_wp_error( $finished );

		$response = array(
			'success' => $success,
			'message' => ! $success ? $finished->get_error_message() : __( 'Success!', 'learnpress' )
		);

		if ( $success ) {
			$attempts            = $user_quiz->get_attempts( array( 'limit' => 1 ) );
			$response['results'] = $attempts[0];
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Hint the question and response hint content.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function hint_answer( $request ) {
		$question_id = $request['question_id'];
		$hintCount   = $this->userItem->hint( $question_id );
		$question    = learn_press_get_question( $question_id );

		// Response
		$response = array(
			'hint' => $question->get_hint()
		);

		return rest_ensure_response( $response );
	}

	public function check_answer( $request ) {
		$question_id = $request['question_id'];
		$answered    = $request['answered'];

		$this->userItem->add_question_answer( $question_id, $answered );
		$this->userItem->update();
		$hintCount = $this->userItem->check_question( $question_id );
		$this->userItem->update();

		$question = learn_press_get_question( $question_id );

		// Response
		$response = array(
			'explanation' => $question->get_explanation(),
			'options'     => xxx_get_question_options_for_js( $question, array( 'include_is_true' => true ) ),
			'result'      => $question->check( $answered ),
			'answered'    => $answered
		);

		return rest_ensure_response( $response );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$settings = LP()->settings();
		$response = array(
			'result' => $settings->get()
		);

		return rest_ensure_response( $response );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_item( $request ) {
		$settings = LP()->settings();
		$response = array(
			'result' => $settings->get( $request['key'] )
		);

		return rest_ensure_response( $response );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function update_item( $request ) {
		$response = array();
		$settings = LP()->settings();
		$option   = $settings->get( $request['key'] );

		$settings->update( $request['key'], $request['data'] );
		$new_option = $settings->get( $request['key'] );
		$success    = maybe_serialize( $option ) !== maybe_serialize( $new_option );

		$response['success'] = $success;
		$response['result']  = $success ? $new_option : $option;

		return rest_ensure_response( $response );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function delete_item( $request ) {
		$response = array();

		return rest_ensure_response( $response );
	}
}