var app = angular.module("App", [
  "angular-svg-round-progressbar",
  "ngMaterial",
  "ngMessages",
  "ui.bootstrap",
  "ngAnimate"
]);

app.filter("limit", function() {
  return function(val, byLastSpace, end, maxLen) {
    if (!val) {
      return "";
    }

    maxLen = parseInt(maxLen);
    if (!maxLen) {
      return val;
    }
    if (val.length <= maxLen) {
      return val;
    }

    val = val.substring(0, maxLen);
    if (byLastSpace) {
      let lastWhiteSpace = val.lastIndexOf(" ");
      if (lastWhiteSpace !== -1) {
        val = val.substring(0, lastWhiteSpace);
      }
    }
    return val + (end || " ...");
  };
});

app.filter("between", function() {
  return function(str, firstChar, lastChar) {
    if (!str) {
      return "";
    }

    newStr = str.substring(
      str.lastIndexOf(firstChar) + 1,
      str.lastIndexOf(lastChar)
    );
    return newStr;
  };
});

app.controller("MainController", function($scope, $http) {
  $scope.detailButtonEnabled = false;
  $scope.showDetails = false;
  $scope.resultsTable = "";
  $scope.showProgressBar = false;
  $scope.showProductTable = false;
  $scope.wishListEnabled = false;
  // $scope.resultsTableRecords = false;

  $scope.getResults = function() {
    $scope.showProgressBar = true;
    $scope.showProductTable = true;
    $scope.wishListEnabled = false;
    $scope.showProgressBar = false;
  };

  $http.get("http://ip-api.com/json").then(function(response) {
    $scope.Zip = response.data.zip;
  });
  $scope.Location1 = "here";
  $scope.Distance = "10";

  $scope.productSearchFormSubmit = function() {
    $scope.showProgressBar = true;
    $scope.showDetails = false;
    let queryParams = {
      Keyword: $scope.Keyword,
      Category: $scope.Category,
      Condition: {
        New: $scope.NewCondition,
        Used: $scope.UsedCondition,
        Unspecified: $scope.UnspecifiedCondition
      },
      Shipping: {
        Local: $scope.LocalShipping,
        Free: $scope.FreeShipping
      },
      Distance: $scope.Distance
    };
    if ($scope.Location1 === "here") {
      queryParams.Zipcode = $scope.Zip;
    }
    if ($scope.Location1 === "other") {
      // // console.log("Other");
      queryParams.Zipcode = $scope.productSearchForm.zipcode.$viewValue;
      // console.log(queryParams.Zipcode);
    }
    // console.log($scope.Zip);
    // console.log($scope.Location1);
    // console.log(queryParams);
    $http({
      method: "GET",
      url: "/getProducts",
      params: queryParams
    })
      .then(function(response) {
        $scope.items = response.data.findItemsAdvancedResponse;
        // console.log($scope.items);
        $scope.resultsTable =
          response.data.findItemsAdvancedResponse[0].searchResult[0].item;
        // console.log("Results Table", $scope.resultsTable);
        $scope.showProgressBar = false;
        $scope.showProductTable = true;
        // console.log($scope.resultsTable);

        // for(let i = 0; i < $scope.resultsTable.length; i++) {
        //   if($scope.resultsTable[i].itemId in localStorage) {
        //     document.getElementById($scope.resultsTable[i].itemId).innerText = 'remove_shopping_cart';
        //     document.getElementById($scope.resultsTable[i].itemId).style.color = 'gold';
        //   }
        // }
        if ($scope.resultsTable !== undefined) {
          $scope.resultsTableRecords = true;
          $scope.totalItems =
            response.data.findItemsAdvancedResponse[0].searchResult[0].item.length;
          $scope.currentPage = 1;
          $scope.itemsPerPage = 10;
          $scope.maxSize = 5;

          $scope.$watch("currentPage", function() {
            setPagingData($scope.currentPage);
          });

          function setPagingData(page) {
            let pagedData = $scope.resultsTable.slice(
              (page - 1) * $scope.itemsPerPage,
              page * $scope.itemsPerPage
            );
            $scope.filteredResults = pagedData;
          }
        } else {
          $scope.resultsTableRecords = false;
        }
      })
      .catch(function(err) {
        console.warn(err);
      });
  };

  $scope.reset = function() {
    $scope.showDetails = false;
    $scope.detailButtonEnabled = false;
    $scope.productSearchForm.$setPristine();
    $scope.productSearchForm.$setUntouched();
    $scope.showProgressBar = false;
    $scope.wishListEnabled = false;
    $scope.showProductTable = false;
    $scope.productDetails = null;
    $scope.resultsTable = null;
    $scope.resultsTableRecords = null;
  };

  $scope.productSearchFormSubmitAgain = function() {
    $scope.showProgressBar = true;
    $scope.showDetails = false;
    let queryParams = {
      Keyword: $scope.Keyword,
      Category: $scope.Category,
      Condition: {
        New: $scope.NewCondition,
        Used: $scope.UsedCondition,
        Unspecified: $scope.UnspecifiedCondition
      },
      Shipping: {
        Local: $scope.LocalShipping,
        Free: $scope.FreeShipping
      },
      Distance: $scope.Distance
    };
    if ($scope.Location1 === "here") {
      queryParams.Zipcode = $scope.Zip;
    }
    if ($scope.Location1 === "other") {
      // console.log("Other");
      queryParams.Zipcode = $scope.productSearchForm.zipcode.$viewValue;
      // console.log(queryParams.Zipcode);
    }
    // console.log($scope.Zip);
    // console.log($scope.Location1);
    // console.log(queryParams);
    $http({
      method: "GET",
      url: "/getProducts",
      params: queryParams
    })
      .then(function(response) {
        $scope.items = response.data.findItemsAdvancedResponse;
        $scope.resultsTable =
          response.data.findItemsAdvancedResponse[0].searchResult[0].item;
        $scope.showProgressBar = false;
        $scope.showProductTable = true;
        $scope.detailButtonEnabled = true;
        let temp = $scope.resultsTable;

        for (let i = 0; i < temp.length; i++) {
          if ($scope.highlightedItemId in temp[i]) {
            setSelectedRow(temp[i], i);
          }
        }

        if ($scope.resultsTable !== undefined) {
          $scope.resultsTableRecords = true;
          $scope.totalItems =
            response.data.findItemsAdvancedResponse[0].searchResult[0].item.length;
          $scope.currentPage = 1;
          $scope.itemsPerPage = 10;
          $scope.maxSize = 5;

          $scope.$watch("currentPage", function() {
            setPagingData($scope.currentPage);
          });

          function setPagingData(page) {
            let pagedData = $scope.resultsTable.slice(
              (page - 1) * $scope.itemsPerPage,
              page * $scope.itemsPerPage
            );
            $scope.filteredResults = pagedData;
          }
        } else {
          $scope.resultsTableRecords = false;
        }
      })
      .catch(function(err) {
        console.warn(err);
      });
  };

  $scope.validateForm = function(searchText) {
    return !isNaN(searchText) && searchText && searchText.length === 5;
  };

  $scope.query = function(searchText) {
    return $http
      .get("/autoCompleteZipCode/" + encodeURI(searchText))
      .then(function(response) {
        // console.log(response.data);
        return response.data;
      })
      .catch(function(error) {
        console.warn(error.status);
      });
  };

  $scope.productDetailsSearch = function(
    itemId,
    shippingInfo,
    returnsAccepted
  ) {
    $scope.showProductTable = false;
    $scope.showProgressBar = true;
    $scope.productShippingInfo = shippingInfo;
    $scope.productReturns = returnsAccepted;
    $scope.ItemId = itemId;
    // console.log(
    //   "Product Shipping Info",
    //   $scope.productShippingInfo[0].shippingServiceCost[0].__value__
    // );
    // console.log("Item ID search started on: " + encodeURI(itemId));
    return $http
      .get("/productDetails/" + encodeURI(itemId))
      .then(function(response) {
        // console.log(response.data.Item);
        $scope.showProgressBar = false;
        $scope.productDetails = response.data.Item;
        // console.log("Product Details ", $scope.productDetails);
        // console.log($scope.productDetails.Subtitle);
        // console.log("Item search finished");
        $scope.detailButtonEnabled = false;
        $scope.showDetails = true;
        $scope.showProductTable = false;
      })
      .catch(function(error) {
        console.warn(error.status);
      });
  };

  $scope.selectedRow = null;
  $scope.setSelectedRow = function(x, index) {
    $scope.selectedRow = index;
    $scope.detailButtonEnabled = true;
    $scope.highlightedItemId = x.itemId;
    $scope.highlightedShipping = x.shippingInfo;
    $scope.highlightedReturns = x.returnsAccepted;
  };

  $scope.productDetailButtonSearch = function(
    itemId,
    shippingInfo,
    returnsAccepted
  ) {
    // console.log(itemId, shippingInfo, returnsAccepted);
    $scope.showProductTable = false;
    $scope.showProgressBar = true;
    $scope.productShippingInfo = $scope.highlightedShipping;
    $scope.productReturns = $scope.highlightedReturns;
    $scope.ItemId = $scope.highlightedItemId;

    return $http
      .get("/productDetails/" + encodeURI($scope.ItemId))
      .then(function(response) {
        // console.log(response.data.Item);
        $scope.showProgressBar = false;
        $scope.productDetails = response.data.Item;
        // console.log("Product Details ", $scope.productDetails);
        // console.log($scope.productDetails.Subtitle);
        // console.log("Item search finished");
        $scope.detailButtonEnabled = false;
        $scope.showDetails = true;
        $scope.showProductTable = false;
      })
      .catch(function(error) {
        console.warn(error.status);
      });
  };

  $scope.productPhotos = function(itemTitle) {
    // console.log("Google Custom Photos");
    // console.log(itemTitle);
    return $http
      .get("/productPhotos/" + encodeURIComponent(itemTitle))
      .then(function(response) {
        // console.log(response.data);
        // console.log(response.data.items);
        $scope.photosOfItem = response.data.items;
        // $scope.photosJson = response.data.items;
      })
      .catch(function(error) {
        console.warn(error.status);
      });
  };

  $scope.feedback_rating = function(feedbackRating) {
    $scope.useStarBorder = true;
    let starRating = {
      None: 9,
      Yellow: 49,
      Blue: 99,
      Turquoise: 499,
      Purple: 999,
      Red: 4999,
      Green: 9999,
      YellowShooting: 24999,
      TurquoiseShooting: 49999,
      PurpleShooting: 99999,
      RedShooting: 499999,
      GreenShooting: 999999,
      SilverShooting: 1000000
    };
    $scope.useStarBorder = starRating[feedbackRating] < 10000;
    // console.log($scope.useStarBorder);
    // console.log("Feedback Rating", feedbackRating);
  };

  $scope.sortByField = "Default";
  $scope.reversed = false;
  $scope.sort_function = function(sortType) {
    // console.log("Inside sort function " + sortType);
    $scope.sortByField = sortType;
    // console.log($scope.sortByField);
  };

  $scope.ascending = function(val) {
    // console.log("Ascending called");
    $scope.reversed = val;
    // console.log($scope.reversed);
  };

  $scope.dscending = function(val) {
    // console.log("Descending called");
    $scope.reversed = val;
    // console.log($scope.reversed);
  };

  $scope.similarProducts = function(itemId) {
    // console.log("Fetching products similar to item id: ", itemId);
    return $http
      .get("/similarItems/" + encodeURI(itemId))
      .then(function(response) {
        // console.log(response.data);
        $scope.similarItems =
          response.data.getSimilarItemsResponse.itemRecommendations.item;
      })
      .catch(function(err) {
        console.warn(err.status);
      });
  };

  $scope.maxItems = 5;
  $scope.showMoreItems = function() {
    // console.log($scope.similarItems.length);
    $scope.maxItems = $scope.similarItems.length;
  };

  $scope.showLessItems = function() {
    // console.log($scope.similarItems.length);
    $scope.maxItems = 5;
  };

  $scope.share = function() {
    let shareItemDetails = $scope.productDetails;
    let url =
      "https://www.facebook.com/dialog/share?app_id=921786024827144&display=popup&quote=Buy " +
      encodeURI(shareItemDetails.Title) +
      " at $" +
      encodeURI(shareItemDetails.CurrentPrice.Value) +
      " from link below\n" +
      "&href=" +
      encodeURIComponent(shareItemDetails.ViewItemURLForNaturalSearch);
    window.open(url, "popup");
  };

  $scope.wishList = {};
  $scope.wishListEnabled = false;
  // $scope.removeShoppingCart = false;
  $scope.addToWishList = function(x) {
    if (document.getElementById(x.itemId).innerText === "add_shopping_cart") {
      document.getElementById(x.itemId).innerText = "remove_shopping_cart";
      document.getElementById(x.itemId).style.color = "gold";
      if (!(x.itemId in localStorage)) {
        localStorage.setItem(x.itemId, JSON.stringify(x));
        $scope.wishList[x.itemId] = x;
      } else {
        localStorage.removeItem(x.itemId);
        delete $scope.wishList[x.itemId];
      }
    } else if (
      document.getElementById(x.itemId).innerText === "remove_shopping_cart"
    ) {
      document.getElementById(x.itemId).innerText = "add_shopping_cart";
      document.getElementById(x.itemId).style.color = "black";
      localStorage.removeItem(x.itemId);
      delete $scope.wishList[x.itemId];
      // console.log(x.itemId);
    }
    // console.log($scope.wishList);
  };

  $scope.getWishList = function() {
    $scope.showProgressBar = true;
    // console.log($scope.wishListRecords);
    // console.log($scope.wishList);
    $scope.wishList = {};
    $scope.wishListArray = [];
    $scope.wishListEnabled = true;
    $scope.showProductTable = false;
    let keys = Object.keys(localStorage);
    // console.log(keys);
    for (i = 0; i < localStorage.length; i++) {
      $scope.wishList[localStorage.key(i)] = JSON.parse(
        localStorage.getItem(localStorage.key(i))
      );
      $scope.wishListArray.push(JSON.parse(localStorage.getItem(keys[i])));
    }
    $scope.wishListRecords = $scope.wishListArray.length;
    // console.log($scope.wishListArray);

    for (let j in $scope.wishListArray) {
      // console.log($scope.wishListArray[j]);
    }
    $scope.showProgressBar = false;
  };

  $scope.removeFromWishList = function(x) {
    localStorage.removeItem(x.itemId);
    delete $scope.wishList[x.itemId];
    $scope.getWishList();
    // console.log($scope.wishListRecords);
  };

  $scope.getTotalShopping = function() {
    let sum = 0;
    if ($scope.wishListArray !== undefined) {
      for (let i = 0; i < $scope.wishListArray.length; i++) {
        let item = $scope.wishListArray[i];
        sum += parseFloat(item.sellingStatus[0].currentPrice[0].__value__);
      }
    }
    return sum;
  };
});
