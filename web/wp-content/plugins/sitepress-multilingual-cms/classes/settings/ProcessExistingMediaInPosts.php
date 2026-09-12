<?php

namespace WPML\TM\Settings;

use WPML\Collect\Support\Collection;
use WPML\Core\BackgroundTask\Command\UpdateBackgroundTask;
use WPML\Core\BackgroundTask\Model\BackgroundTask;
use WPML\BackgroundTask\AbstractTaskEndpoint;
use WPML\Core\BackgroundTask\Service\BackgroundTaskService;
use WPML\FP\Obj;
use WPML\MediaTranslation\PostWithMediaFilesFactory;

class ProcessExistingMediaInPosts extends AbstractTaskEndpoint {
	const LOCK_TIME         = 5;
	const MAX_RETRIES       = 10;
	const DESCRIPTION       = 'Processing media in posts.';
	const POSTS_PER_REQUEST = 10;

	/** @var \wpdb */
	private $wpdb;

	/** @var PostWithMediaFilesFactory $postWithMediaFilesFactory */
	private $postWithMediaFilesFactory;

	/**
	 * @param \wpdb                     $wpdb
	 * @param UpdateBackgroundTask      $updateBackgroundTask
	 * @param BackgroundTaskService     $backgroundTaskService
	 * @param PostWithMediaFilesFactory $postWithMediaFilesFactory
	 */
	public function __construct(
		\wpdb $wpdb,
		UpdateBackgroundTask $updateBackgroundTask,
		BackgroundTaskService $backgroundTaskService,
		PostWithMediaFilesFactory $postWithMediaFilesFactory
	) {
		$this->wpdb                      = $wpdb;
		$this->postWithMediaFilesFactory = $postWithMediaFilesFactory;

		parent::__construct( $updateBackgroundTask, $backgroundTaskService );
	}

	public function runBackgroundTask( BackgroundTask $task ) {
		$payload = $task->getPayload();
		$page    = Obj::propOr( 1, 'page', $payload );
		$postIds = $this->getPosts( $page );

		if ( count( $postIds ) > 0 ) {
			$this->processExistingMediaInPosts( $postIds );
			$payload['page'] = $page + 1;
			$task->setPayload( $payload );
			$task->addCompletedCount( count( $postIds ) );
			$task->setRetryCount(0 );
			if ( $task->getCompletedCount() >= $task->getTotalCount() ) {
				$task->finish();
			}
		} else {
			$task->finish();
		}
		return $task;
	}

	public function getDescription( Collection $data ) {
		return __( self::DESCRIPTION, 'sitepress' );
	}

	public function getTotalRecords( Collection $data ) {
		return $this->getPostsCount();
	}

	/**
	 * @param int $page
	 *
	 * @return array
	 */
	private function getPosts( $page ) {
		return $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT DISTINCT p.ID
						FROM {$this->wpdb->posts} AS p
						INNER JOIN {$this->wpdb->prefix}icl_translations t ON t.element_id = p.ID
						WHERE t.source_language_code IS NULL
						AND t.element_type LIKE 'post_%'
						AND p.post_status NOT IN ('auto-draft', 'trash', 'inherit')
						ORDER BY p.ID ASC
						LIMIT %d OFFSET %d",
				self::POSTS_PER_REQUEST,
				($page-1)*self::POSTS_PER_REQUEST
			)
		);
	}

	/**
	 * @return int
	 */
	private function getPostsCount() {
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $this->wpdb->get_var(
			"SELECT COUNT(DISTINCT(p.ID))
					FROM {$this->wpdb->posts} AS p
					INNER JOIN {$this->wpdb->prefix}icl_translations t ON t.element_id = p.ID
					WHERE t.source_language_code IS NULL
					AND t.element_type LIKE 'post_%'
					AND p.post_status NOT IN ('auto-draft', 'trash', 'inherit')"
		);
	}

	/**
	 * @param array $postIds
	 */
	private function processExistingMediaInPosts( array $postIds ) {
		foreach ( $postIds as $postId ) {
			$postMedia = $this->postWithMediaFilesFactory->create( $postId );
			$postMedia->extract_and_save_media_ids();
		}
	}
}
