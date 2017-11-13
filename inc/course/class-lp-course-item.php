<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class LP_Course_Item
 */
class LP_Course_Item extends LP_Abstract_Post_Data implements ArrayAccess {

	/**
	 * The icon maybe used somewhere.
	 *
	 * @var string
	 */
	protected $_icon_class = '';

	/**
	 * The type of item.
	 *
	 * @var string
	 */
	protected $_item_type = '';

	/**
	 * @var LP_Course
	 */
	protected $_course = null;

	/**
	 * @var LP_Course_Section
	 */
	protected $_section = null;

	protected $_nonces = array();

	/**
	 * @var null
	 */
	protected $_content = null;

	/**
	 * LP_Course_Item constructor.
	 *
	 * @param $item mixed
	 * @param $args array
	 */
	public function __construct( $item, $args = null ) {
		parent::__construct( $item, $args );
		$this->add_support( 'comments', get_post_field( 'comment_status', $this->get_id() ) === 'open' );

		// Set default data
		$this->set_data(
			array(
				'duration' => get_post_meta( $this->get_id(), '_lp_duration', true )
			)
		);
	}

	/**
	 * @return string
	 */
	public function get_item_type() {
		return $this->_item_type;
	}

	/**
	 * @return string
	 */
	public function get_icon_class() {
		return $this->_icon_class;
	}

	/**
	 *
	 */
	public function is_preview() {
		return get_post_meta( $this->get_id(), '_lp_preview', true ) == 'yes';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return apply_filters( 'learn-press/course-item-title', parent::get_title(), $this->get_id() );
	}

	public function get_format() {
		return ( false !== ( $format = wp_cache_get( 'item-format-' . $this->get_id(), 'lp-item-formats' ) ) ) ? $format : get_post_format( $this->get_id() );
	}

	/**
	 * Return true if item can be shown in course curriculum.
	 *
	 * @return mixed
	 */
	public function is_visible() {
		/* section item display inside a section */
		$allow_items = learn_press_get_course_item_types();

		$item_type = get_post_type( $this->get_id() );

		// If item type does not allow
		$show = true;// in_array( $item_type, $allow_items );

		return apply_filters( 'learn-press/course-item-visible', $show, $this->get_item_type(), $this->get_id() );
	}

	/**
	 * Get class of item.
	 *
	 * @param string $more
	 * @param int    $user_id
	 *
	 * @return array
	 */
	public function get_class( $more = '', $user_id = 0 ) {
		global $lp_course_item;

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$defaults = array(
			'course-item',
			'course-item-' . $this->get_item_type(),
			'course-item-' . $this->get_id()
		);

		if ( ( 'standard' !== ( $post_format = $this->get_format() ) ) && $post_format ) {
			$defaults[] = 'course-item-type-' . $post_format;
		}

		if ( $lp_course_item && $lp_course_item->get_id() == $this->get_id() ) {
			$defaults[] = 'current';
		}

		if ( is_array( $more ) ) {
			$defaults = array_merge( $defaults, $more );
		} else {
			$defaults[] = $more;
		}

		$course_id = 0;
		$enrolled  = false;
		if ( $this->is_preview() ) {
			$defaults[] = 'item-preview';
		} else {
			if ( $course = $this->get_course() ) {
				$course_id = $course->get_id();
				if ( $course->is_free() && ! $course->is_require_enrollment() ) {
					$defaults[] = 'item-free';
				} else {
					if ( $user = learn_press_get_user( $user_id, ! $user_id ) ) {
						if ( $enrolled = $user->has_enrolled_course( $course_id ) ) {
							$item_status = $user->get_item_status( $this->get_id(), $course_id );
							$item_grade  = $user->get_item_grade( $this->get_id(), $course_id );

							if ( $item_status ) {
								$defaults[] = 'has-status';
								///$status_classes[] = 'has-status';
								$defaults[] = 'status-' . $item_status;
							}
							switch ( $item_status ) {
								case 'started':
									break;
								case 'completed':
									$defaults[] = $item_grade;
							}
						}
					}
					if ( ! $enrolled ) {
						$defaults[] = 'item-locked';
					}
				}
			} else {
				$defaults[] = 'item-locked';
			}
		}

		if ( ! $enrolled ) {

		}

		$classes = apply_filters( 'learn-press/course-item-class', $defaults, $this->get_item_type(), $this->get_id(), $course_id );

		// Filter unwanted values
		$classes = is_array( $classes ) ? $classes : explode( ' ', $classes );
		$classes = array_filter( $classes );
		$classes = array_unique( $classes );

		return $classes;
	}

	/**
	 * Get permalink of item inside course.
	 *
	 * @return string
	 */
	public function get_permalink() {
		$link = false;
		if ( $this->_course ) {
			$link = $this->_course->get_item_link( $this->get_id() );
		}

		return apply_filters( 'learn-press/course-item-link', $link, $this );
	}

	/**
	 * Set course parent of this item.
	 *
	 * @param LP_Course|int $course
	 */
	public function set_course( $course ) {
		if ( is_numeric( $course ) ) {
			$this->_course = learn_press_get_course( $course );
		} else {
			$this->_course = $course;
		}
	}

	/**
	 * Return course.
	 *
	 * @return LP_Course
	 */
	public function get_course() {
		return $this->_course;
	}

	/**
	 * @param LP_Course_Section $section
	 */
	public function set_section( $section ) {
		$this->_section = $section;
	}

	/**
	 * @return LP_Course_Section
	 */
	public function get_section() {
		return $this->_section;
	}

	/**
	 * Get instance of an item from post
	 *
	 * @param WP_Post|int $post
	 *
	 * @return LP_Course_Item
	 */
	public static function get_item( $post ) {
		$item      = false;
		$item_type = '';
		$item_id   = 0;

		if ( is_numeric( $post ) && $post > 0 ) {
			$post = get_post( $post );
		}

		if ( isset( $post->ID ) ) {
			$item_type = $post->post_type;
			$item_id   = $post->ID;
		}

		if ( $item_type ) {
			if ( learn_press_is_support_course_item_type( $item_type ) ) {
				$type = str_replace( 'lp_', '', $item_type );

				$class = apply_filters( 'learn-press/course-item-object-class', false, $type, $item_type, $item_id );

				if ( is_string( $class ) && class_exists( $class ) ) {
					$item = new $class( $post->ID, $post );
				} elseif ( $class instanceof LP_Course_Item ) {
					$item = $class;
				}

				if ( ! $item ) {

					switch ( $type ) {
						case 'lesson':
							$item = LP_Lesson::get_lesson( $item_id );
							break;
						case 'quiz':
							$item = LP_Quiz::get_quiz( $item_id );
							break;
					}
				}
			}
		}

		return apply_filters( 'learn-press/get-course-item', $item, $item_type, $item_id );
	}

	public function get_user_status( $user ) {
		if ( $course = $this->get_course() ) {
			return $course->get_id();
		}

		return $course;
	}

	/**
	 * Get template name of item.
	 *
	 * @return string
	 */
	public function get_template() {
		$item_type = $this->get_item_type();

		return apply_filters( 'learn-press/section-item-template', 'item-' . str_replace( 'lp_', '', $item_type ), $item_type );
	}

	/**
	 * To array.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function to_array() {
		$post = get_post( $this->get_id() );

		return array(
			'id'    => $this->get_id(),
			'type'  => $this->get_item_type(),
			'title' => $post->post_title,
		);
	}

	/**
	 * Create nonce for checking actions on an item.
	 *
	 * @param string $action
	 * @param int    $course_id
	 * @param int    $user_id
	 *
	 * @return string
	 */
	public function create_nonce( $action = '', $course_id = 0, $user_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = $this->get_course()->get_id();
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$action = $this->get_nonce_action( $action, $course_id, $user_id );

		return wp_create_nonce( $action );
	}

	/**
	 * Verify nonce for an action on item.
	 *
	 * @param string $nonce
	 * @param string $action
	 * @param int    $course_id
	 * @param int    $user_id
	 *
	 * @return false|int
	 */
	public function verify_nonce( $nonce, $action = '', $course_id = 0, $user_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = $this->get_course()->get_id();
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$action = $this->get_nonce_action( $action, $course_id, $user_id );

		return wp_verify_nonce( $nonce, $action );
	}

	public function get_nonce_action( $action, $course_id, $user_id ) {
		return sprintf( '%s-item-%d-%d-%d', $action, $user_id, $course_id, $this->get_id() );
	}

	public function is_viewing_question( $question_id = 0 ) {
		global $lp_quiz_question;
		if ( $question_id ) {
			$viewing = $lp_quiz_question && $lp_quiz_question->get_id() == $question_id;
		} else {
			$viewing = $lp_quiz_question ? $lp_quiz_question->get_id() : false;
		}

		return apply_filters( 'learn-press/quiz/is-viewing-question', $viewing, $question_id, $this->get_id() );
	}

	public function get_status_classes( $user_id = 0, $course_id = 0 ) {
		$status_classes = array();
		if ( $course = learn_press_get_course( $course_id ) ) {
			if ( $this->is_preview() ) {
				$status_classes[] = 'item-preview';
			} elseif ( $course->is_free() && ! $course->is_require_enrollment() ) {
				$status_classes[] = 'item-free';
			}
		}

		if ( $user = learn_press_get_user( $user_id, ! $user_id ) ) {
			$item_status = $user->get_item_status( $this->get_id(), $course_id );
			$item_grade  = $user->get_item_grade( $this->get_id(), $course_id );

			if ( $item_status ) {
				$status_classes[] = 'course-item-status';
				///$status_classes[] = 'has-status';
				$status_classes[] = 'item-' . $item_status;
			}
			switch ( $item_status ) {
				case 'started':
					break;
				case 'completed':
					$status_classes[] = $item_grade;
			}
		}

		return apply_filters( 'learn-press/item-status-classes', $status_classes, $this->get_id(), $course_id, $user_id );
	}

	public function get_duration() {
		$duration = $this->get_data( 'duration' );
		if ( ! is_numeric( $duration ) ) {
			$duration = strtotime( '+' . $duration, 0 );
		}

		return $duration;
	}

	public function offsetExists( $offset ) {

	}

	public function offsetGet( $offset ) {

	}

	public function offsetSet( $offset, $value ) {

	}

	public function offsetUnset( $offset ) {
	}
}