/**
 *  Multidatabases DatabaseMetadatas JS
 *  メタデータ編集関連フロントエンド処理
 *  webroot/js/edit_multi_database_metadatas.js
 *
 *  @author ohno.tomoyuki@ricksoft.jp (Tomoyuki OHNO/Ricksoft, Co., Ltd.)
 *  @link http://www.netcommons.org NetCommons Project
 *  @license http://www.netcommons.org/license.txt NetCommons License
 */

NetCommonsApp.controller('MultidatabaseMetadata', ['$scope', function($scope) {

  $scope.metadataGroup0 = [];
  $scope.metadataGroup1 = [];
  $scope.metadataGroup2 = [];
  $scope.metadataGroup3 = [];
  $scope.metadataEdit = {};

  /**
     * initialize
     *
     * @param {Object} data
     * @type {Object}
     */
  $scope.initialize = function(data) {
    angular.forEach(data.multidatabaseMetadata, function(value) {
      switch (value.position) {
        case 0:
          value.rank = $scope.metadataGroup0.length;
          $scope.metadataGroup0.push(value);
          break;
        case 1:
          value.rank = $scope.metadataGroup1.length;
          $scope.metadataGroup1.push(value);
          break;
        case 2:
          value.rank = $scope.metadataGroup2.length;
          $scope.metadataGroup2.push(value);
          break;
        case 3:
          value.rank = $scope.metadataGroup3.length;
          $scope.metadataGroup3.push(value);
          break;
      }
    });
  };

  /**
     * グループに対応するメタデータのオブジェクトを返す
     *
     * @param {Number} positionNo
     * @return {Boolean|Object}
     */
  function getGroup(positionNo) {
    switch (positionNo) {
      case 0:
        return $scope.metadataGroup0;
      case 1:
        return $scope.metadataGroup1;
      case 2:
        return $scope.metadataGroup2;
      case 3:
        return $scope.metadataGroup3;
      default:
        return false;
    }
  }

  /**
     * メタデータ項目を追加する
     *
     * @param {Number} positionNo
     * @param {Number} last
     * @type {Object}
     */
  $scope.add = function(positionNo, last) {
    var nextRank = last + 1;
    var value = {
      col_no: '',
      id: '',
      is_title: '',
      is_visible_field_name: 1,
      is_file_dl_require_auth: '',
      is_visible_file_dl_conter: '',
      is_require: '',
      is_searchable: '',
      is_sortable: '',
      is_visible_detail: 1,
      is_visible_list: 1,
      key: '',
      language_id: '',
      name: '無題',
      position: positionNo,
      rank: nextRank,
      selections: '',
      type: 'text'
    };
    var currentMetadatas = getGroup(positionNo);
    currentMetadatas.push(value);
  };

  /**
     * メタデータ項目を削除する
     *
     * @param {Object} $event
     * @param {Number} positionNo
     * @param {Number} index
     * @param {String} message
     * @type {Object}
     */
  $scope.delete = function($event, positionNo, index, message) {
    $scope.eventStop($event);

    if (!confirm(message)) {
      return false;
    }
    var currentMetadatas = getGroup(positionNo);
    currentMetadatas.splice(index, 1);
  };

  /**
     * メタデータ項目を行移動する
     *
     * @param {String} type
     * @param {Number} positionNo
     * @param {Number} rank
     * @type {Object}
     */
  $scope.moveRank = function(type, positionNo, rank) {
    var dest = (type === 'up') ? rank - 1 : rank + 1;

    var currentMetadatas = getGroup(positionNo);

    var destMetadata = angular.copy(currentMetadatas[dest]);
    var targetMetadata = angular.copy(currentMetadatas[rank]);
    currentMetadatas[rank] = destMetadata;
    currentMetadatas[dest] = targetMetadata;

    currentMetadatas[rank].position = destMetadata.position;
    currentMetadatas[rank].rank = rank;
    currentMetadatas[dest].position = targetMetadata.position;
    currentMetadatas[dest].rank = dest;
  };

  /**
     * メタデータ項目をグループ移動する
     *
     * @param {Number} destPositionNo
     * @param {Number} currentPositionNo
     * @param {Number} rank
     * @type {Object}
     */
  $scope.movePosition = function(destPositionNo, currentPositionNo, rank) {

    var currentMetadatas = getGroup(currentPositionNo);
    var destMetadatas = getGroup(destPositionNo);

    if (currentMetadatas == false) {
      return false;
    }

    if (destMetadatas == false) {
      return false;
    }

    var targetMetadata = angular.copy(currentMetadatas[rank]);

    destMetadatas.push(targetMetadata);
    currentMetadatas.splice(rank, 1);

    targetMetadata.position = destPositionNo;
    targetMetadata.rank = destMetadatas.length - 1;
  };

  /**
     * 単一選択・複数選択用の選択肢を追加する
     *
     * @param {Number} positionNo
     * @param {Number} parentIndex
     * @type {Object}
     */
  $scope.addSelection = function(positionNo, parentIndex) {
    var currentMetadatas = getGroup(positionNo);
    var currentMetadata = currentMetadatas[parentIndex];
    var selection = '';
    currentMetadata.selections.push(selection);
  };

  /**
     * 単一選択・複数選択用の選択肢を削除する
     *
     * @param {Number} positionNo
     * @param {Number} parentIndex
     * @param {Number} index
     * @type {Object}
     */
  $scope.delSelection = function(positionNo, parentIndex, index) {
    var currentMetadatas = getGroup(positionNo);
    var currentMetadata = currentMetadatas[parentIndex];
    currentMetadata.selections.splice(index, 1);
  };

  /**
     * 単一選択・複数選択用の選択肢を削除する
     *
     * @param {Number} positionNo
     * @param {Number} parentIndex
     * @param {Number} index
     * @type {Object}
     */
  $scope.moveSelection = function(type, positionNo, parentIndex, index) {
    var currentMetadatas = getGroup(positionNo);
    var currentMetadata = currentMetadatas[parentIndex];

    var dest = (type === 'up') ? index - 1 : index + 1;

    if (angular.isUndefined(currentMetadata.selections[dest])) {
      return false;
    }

    var destSelection = angular.copy(currentMetadata.selections[dest]);
    var targetSelection = angular.copy(currentMetadata.selections[index]);
    currentMetadata.selections[index] = destSelection;
    currentMetadata.selections[dest] = targetSelection;
  };

  /**
     * イベントストップ
     *
     * @param {Object} $event
     * @type {Object}
     */
  $scope.eventStop = function($event) {
    $event.preventDefault();
    $event.stopPropagation();
  };
}]);
