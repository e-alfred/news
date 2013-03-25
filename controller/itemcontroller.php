<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Http\Request;

use \OCA\News\Bl\ItemBl;


class ItemController extends Controller {

	private $itemBl;

	public function __construct(API $api, Request $request, ItemBl $itemBl){
		parent::__construct($api, $request);
		$this->itemBl = $itemBl;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function items(){
		$userId = $this->api->getUserId();
		$showAll = $this->api->getUserValue($userId, 'showAll') === 'true';

		$limit = $this->params('limit');
		$type = (int) $this->params('type');
		$id = (int) $this->params('id');

		$this->api->setUserValue($userId, 'lastViewedFeedId', $id);
		$this->api->setUserValue($userId, 'lastViewedFeedType', $type);
		
		if($limit !== null){
			$offset = (int) $this->params('offset', 0);
			$items = $this->itemBl->findAll($id, $type, (int) $limit, $offset, 
				                            $showAll, $userId);
		} else {
			$updatedSince = (int) $this->params('updatedSince');
			$items = $this->itemBl->findAllNew($id, $type, $updatedSince, 
				                               $showAll, $userId);
		}

		$params = array(
			'items' => $items
		);

		return $this->renderJSON($params);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function starred(){
		$userId = $this->api->getUserId();
		$starredCount = $this->itemBl->starredCount($userId);

		$params = array(
			'starred' => $starredCount
		);

		return $this->renderJSON($params);
	}


	private function setStarred($isStarred){
		$userId = $this->api->getUserId();
		$itemId = $this->params('itemId');

		$this->itemBl->star($itemId, $isStarred, $userId);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function star(){
		$this->setStarred(true);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function unstar(){
		$this->setStarred(false);
	}


	private function setRead($isRead){
		$userId = $this->api->getUserId();
		$itemId = (int) $this->params('itemId');

		$this->itemBl->read($itemId, $isRead, $userId);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function read(){
		$this->setRead(true);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function unread(){
		$this->setRead(false);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function readFeed(){
		$userId = $this->api->getUserId();
		$feedId = (int) $this->params('feedId');

		$this->itemBl->readFeed($feedId, $userId);
	}


}