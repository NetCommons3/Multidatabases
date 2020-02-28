/**
 *  Multidatabases Files JS
 *
 *  @author exkazuu@willbooster.com (Kazunori Sakamoto)
 *  @link http://www.netcommons.org NetCommons Project
 *  @license http://www.netcommons.org/license.txt NetCommons License
 */

NetCommonsApp.controller('MultidatabaseFile.view',
    ['$scope', '$http', 'NC3_URL', function($scope, $http, NC3_URL) {

      $scope.downloadCount = '-';

      $scope.init = function(frameId, uploadFileId, initialValue) {
        if (initialValue !== null) {
          $scope.downloadCount = initialValue;
          return;
        }
        var params = '?frame_id=' + frameId + '&upload_file_id=' + uploadFileId;
        $http.get(NC3_URL + '/multidatabases/multidatabase_contents/get_download_count.json' + params)
        .then(
          function(response) {
            $scope.downloadCount = response.data.count.UploadFile.download_count;
          },
          function() {
          });
      };
}]);
