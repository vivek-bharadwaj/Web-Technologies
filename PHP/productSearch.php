<!DOCTYPE html>

<?php
$keyword = "";
$category = "";
$condition = [];
$shipping = [];
$nearby = "";
$distance = "10";
$zipcode = "";
$nearby = "";

$findingApiUrl = "http://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.0&SECURITY-APPNAME=VivekBha-ProductS-PRD-c16e2f149-3c57da65&RESPONSE-DATA-FORMAT=JSON&RESTPAYLOAD&paginationInput.entriesPerPage=20";

$categories = array("Art" => "550", "Baby" => "2984", "Books" => "267", "Clothing, Shoes & Accessories" => "11450", "Computers/Tablets & Networking" => "58058", "Health & Beauty" => "26395", "Music" => "11233", "Video Games & Consoles" => "1249");


?>

<?php

//Array
//(
//    [keyword] => iPhone
//    [category] => Computers/Tablets & Networking
//[condition] => Array
//(
//    [0] => new
//        )
//
//[shipping] => Array
//(
//    [0] => local
//)
//
//[nearby] => yes
//[distance] => 15
//    [from] => here
//[zipcode] =>
//    [search] => Search
//)

if (!empty($_GET)) {
//    print_r($_GET['category']);
    if (isset($_GET['keyword'])) {
        $keyword = $_GET['keyword'];
    }

    if (isset($_GET['category'])) {
        $category = $_GET['category'];
    }

    if (isset($_GET['condition'])) {
        if (count($_GET['condition']) > 0) {
            $condition = $_GET['condition'];
        }
    }

    if (isset($_GET['shipping'])) {
        if (count($_GET['shipping']) > 0) {
            $shipping = $_GET['shipping'];
        }
    }

    if (isset($_GET['nearby'])) {
        if (isset($_GET['distance'])) {
            $distance = $_GET['distance'];
        } else {
            $distance = "10";
        }


        /*
         * if nearby search set - take field zipcode
         * else take zip from geolocation api
         * name = nearby, id = nearby-search
         * name = distance, id = distance, placeholder = 10
         * name = from, id = here, value = here
         * name = from, id = dist type = radio
         * name = zipcode, id = zipcode, placeholder = zip code type = text
         */

        if (isset($_GET['from'])) {
            if ($_GET['from'] == 'here' && isset($_GET['zip'])) {
                $zipcode = $_GET['zip'];
            } else if ($_GET['from'] == 'zipcode' && isset($_GET['zipcode'])) {
                $zipcode = $_GET['zipcode'];
            }
        }
    }

    if ($distance == "") {
        $distance = "10";
    }


    $keywordFilter = "keywords=" . urlencode($keyword);
    $findingApiUrl = $findingApiUrl . "&" . $keywordFilter;

    if (array_key_exists($category, $categories)) {
        $findingApiUrl = $findingApiUrl . "&" . "categoryId" . "=" . urlencode($categories[$category]);
//    print_r($findingApiUrl);
    }
//else print_r($findingApiUrl);

//print_r($zipcode);
    $i = 0;
    if (!empty($zipcode)) {
        $zipcodeFilter = "buyerPostalCode=" . $zipcode;
        $findingApiUrl = $findingApiUrl . "&" . $zipcodeFilter;
        $findingApiUrl = $findingApiUrl . "&" . "itemFilter(" . $i . ").name=" . "MaxDistance" . "&" . "itemFilter(" . $i . ").value=" . $distance;
        $i++;
//    print_r($findingApiUrl . "\r\n\r\n");
    }


//print_r("Distance " . $distance);


    $shippingFilter1 = "";

    if (in_array("free", $shipping)) {
        $shippingFilter1 = "itemFilter(" . $i . ").name=" . "FreeShippingOnly&" . "itemFilter(" . $i . ").value=" . "true";
        $i++;
        $findingApiUrl = $findingApiUrl . "&" . $shippingFilter1;
    }

    $shippingFilter2 = "";
    if (in_array("local", $shipping)) {
        $shippingFilter2 = "itemFilter(" . $i . ").name=" . "LocalPickupOnly&" . "itemFilter(" . $i . ").value=" . "true";
        $i++;
        $findingApiUrl = $findingApiUrl . "&" . $shippingFilter2;
    }

    $findingApiUrl = $findingApiUrl . "&" . "itemFilter(" . $i . ").name=" . "HideDuplicateItems&" . "itemFilter(" . $i . ").value=" . "true";
    $i++;

    $conditionFilter = "";
    $k = "";
    for ($j = 0; $j < count($condition); $j++) {
        $conditionFilter = "itemFilter(" . $i . ").name=" . "Condition&";
        $k = $k . "itemFilter(" . $i . ").value" . "(" . $j . ")" . "=" . $condition[$j] . "&";
    }
    $i++;
    $conditionFilter = $conditionFilter . $k;

    $findingApiUrl = $findingApiUrl . "&" . $conditionFilter;

    $findingApiUrl = rtrim($findingApiUrl, "& ");
//print_r($findingApiUrl);
}

?>

<?php
//print_r($_GET);
$itemId = "";
if (isset($_GET['ItemId'])) {
    $itemId = $_GET['ItemId'];
}

$productDetailsURL = "http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=JSON&appid=VivekBha-ProductS-PRD-c16e2f149-3c57da65&siteid=0&version=967&ItemID=" . $itemId . "&IncludeSelector=Description,Details,ItemSpecifics";

$similarItemsURL = "http://svcs.ebay.com/MerchandisingService?OPERATION-NAME=getSimilarItems&SERVICE-NAME=MerchandisingService&SERVICEVERSION=1.1.0&CONSUMER-ID=VivekBha-ProductS-PRD-c16e2f149-3c57da65&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&itemId=" . $itemId . "&maxResults=8";

?>

<html lang="en">

<head>
    <title>Product Search</title>

    <style>
        body, html, p {
            padding: 0;
            margin: 0;
            color: black;
        }

        label {
            display: inline-block;
        }

        a {
            color: black;
            text-decoration: none;
        }

        a:hover {
            /*color: slategray;*/
            color: #d0d0d0;
        }

        .form-wrapper {
            margin: 40px auto auto;
            width: 600px;
            height: 300px;
            border: 2px solid darkgrey;
            background-color: #FBFBFB;
            position: relative;
        }

        .heading {
            margin-top: 0;
            margin-bottom: 5px;
            font-style: italic;
            font-weight: bold;
            font-size: 33px;
            text-align: center;
            width: 98%;
        }

        .inner {
            border-bottom: 2px solid lightgray;
            margin-left: 5px;
            margin-right: 5px;
            margin-bottom: 16px;
        }

        .label {
            padding-left: 16px;
            margin-bottom: 15px;
        }

        #new, #used, #unspecified {
            margin-left: 15px;
        }

        #local, #free {
            margin-left: 30px;
        }

        #distance {
            margin-left: 20px;
        }

        #dist {
            margin-left: 341px;
        }

        #search-button {
            margin-left: 210px;
        }

        #clear-button {
            margin-left: 5px;
            text-align: center;
        }

        #product-results-table {
            margin-left: auto;
            margin-right: auto;
            margin-top: 40px;
            width: 85%;
            border: 2px solid lightgray;
            border-collapse: collapse;
        }

        th, td, tr {
            border: 2px solid lightgray;
            border-collapse: collapse;

        }

        #product-details-table {
            margin-left: auto;
            margin-right: auto;
            margin-top: 20px;
            /*width: 85%;*/
            border: 2px solid lightgray;
            border-collapse: collapse;
        }

        #product-details-table td {
            padding-left: 10px;
            padding-right: 10px;
        }

        #col-label-1 {
            width: 45px;
        }

        #col-label-2 {
            width: 60px;
            height: 60px;
        }

        .image {
            width: auto;
            height: auto;
            max-width: 80px;
            max-height: 120px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            vertical-align: middle;
        }

        .seller-msg {
            margin-top: 20px;
            text-align: center;
        }

        #seller-message-div {
            display: none;
            margin-bottom: 15px;
        }

        #open-seller-message, #open-similar-items {
            margin-top: 15px;
            margin-bottom: 8px;
            color: #aaaaaa;
            display: none;
        }

        #close-seller-message, #close-similar-items {
            margin-top: 15px;
            margin-bottom: 8px;
            color: #aaaaaa;
            display: none;
        }

        iframe {
            display: block;
            margin: auto;
            overflow: hidden;
            width: 85%;
        }

        .arrow-style {
            margin-top: 5px;
            margin-bottom: 5px;
            width: 70px;
            height: 40px;
        }

        #down-arrow-1, #down-arrow-2 {
            display: none;
        }

        #up-arrow-1, #up-arrow-2 {
            display: none;
        }

        #similar-items-div {
            display: none;
            overflow: auto;
            overflow-y: hidden;
            border: 1px solid #d0d0d0;
            width: 1000px;
            margin: 0 auto 25px;
            white-space: nowrap;
            text-align: center;
        }

        #image-title {
            white-space: normal;
        }

        ul#horizontal li {
            display: inline-block;
        }

    </style>
</head>

<body>
<div class="form-wrapper">
    <div class="heading-wrapper">
        <h2 class="heading">Product Search</h2>
        <div class="wrapper">
            <div class="inner"></div>
        </div>
    </div>
    <form name="myform" class="product-search-form" id="myform" method="GET">
        <p class="label"><b>Keyword</b>
            <input type="search" name="keyword" id="keyword" pattern="^.{2,}$" title="Please enter 2 or more characters"
                   value="<?php if (isset($_GET['keyword'])) echo $_GET['keyword'] ?>" required></p>
        <p class="label"><b>Category</b>
            <select name="category" id="category">
                <option value="All Categories" <?php if (isset($_GET['category']) && $_GET['category'] == 'All Categories') echo 'selected="selected"'; ?> >
                    All Categories
                </option>
                <option value="Art" <?php if (isset($_GET['category']) && $_GET['category'] == 'Art') echo 'selected="selected"'; ?> >
                    Art
                </option>
                <option value="Baby" <?php if (isset($_GET['category']) && $_GET['category'] == 'Baby') echo 'selected="selected"'; ?>>
                    Baby
                </option>
                <option value="Books" <?php if (isset($_GET['category']) && $_GET['category'] == 'Books') echo 'selected="selected"'; ?>>
                    Books
                </option>
                <option value="Clothing, Shoes & Accessories" <?php if (isset($_GET['category']) && $_GET['category'] == 'Clothing, Shoes & Accessories') echo 'selected="selected"'; ?>>
                    Clothing, Shoes & Accessories
                </option>
                <option value="Computers/Tablets & Networking" <?php if (isset($_GET['category']) && $_GET['category'] == 'Computers/Tablets & Networking') echo 'selected="selected"'; ?>>
                    Computers/Tablets & Networking
                </option>
                <option value="Health & Beauty" <?php if (isset($_GET['category']) && $_GET['category'] == 'Health & Beauty') echo 'selected="selected"'; ?>>
                    Health & Beauty
                </option>
                <option value="Music" <?php if (isset($_GET['category']) && $_GET['category'] == 'Music') echo 'selected="selected"'; ?>>
                    Music
                <option value="Video Games & Consoles" <?php if (isset($_GET['category']) && $_GET['category'] == 'Video Games & Consoles') echo 'selected="selected"'; ?>>
                    Video Games & Consoles
                </option>
            </select>
        </p>
        <p class="label"><b>Condition</b>
            <!-- TODO checked property -->
            <!-- Add < ? and ? > tags here php if(isset($_GET['condition']) == "New") echo 'checked="checked"'; -->
            <!--            < ? php if(isset($_GET['condition']) == "Used") echo 'checked="checked"'; ? >-->
            <!--            < ? php if(isset($_GET['condition']) == "Unspecified") echo 'checked="checked"'; ? >-->

            <input type="checkbox" name="condition[]" id="new"
                   value="New" <?php if (isset($_GET['condition']) && in_array("New", $_GET['condition'])) echo 'checked="checked"' ?> >New
            <input type="checkbox" name="condition[]" id="used"
                   value="Used" <?php if (isset($_GET['condition']) && in_array("Used", $_GET['condition'])) echo 'checked="checked"' ?> >Used
            <input type="checkbox" name="condition[]" id="unspecified"
                   value="Unspecified" <?php if (isset($_GET['condition']) && in_array("Unspecified", $_GET['condition'])) echo 'checked="checked"' ?> >Unspecified
        </p>
        <p class="label">
            <b>Shipping Options</b>
            <input type="checkbox" name="shipping[]" id="local"
                   value="local" <?php if (isset($_GET['shipping']) && in_array("local", $_GET['shipping'])) echo 'checked="checked"' ?> >Local
            Pickup
            <input type="checkbox" name="shipping[]" id="free"
                   value="free" <?php if (isset($_GET['shipping']) && in_array("free", $_GET['shipping'])) echo 'checked="checked"' ?> >Free
            Shipping
        </p>
        <p class="label">
            <input type="checkbox" name="nearby" id="nearby-search" value="yes"
                   onchange="setDefaultSearchProperties(this)"
                   <?php if (isset($_GET['nearby']) && $_GET['nearby'] == 'yes') {echo 'checked="checked"';} ?> >
            <b>Enable Nearby Search</b>
            <input type="text" name="distance" id="distance" placeholder="10" pattern="[0-9]+" title="Distance should be a number" size="3"
                   value="<?php if (isset($_GET['distance'])) echo $_GET['distance']; ?>"
                   <?php if (!isset($_GET['nearby'])) echo 'disabled'; ?> >
                   <b> miles from</b>
            <input type="radio" name="from" id="here" value="here"
                   onclick="document.getElementById('zipcode').readOnly = true; document.getElementById('zipcode').value=''; document.getElementById('zipcode').required=false"
                   checked
                   <?php if(!isset($_GET['nearby'])) echo 'disabled'; ?>
            >Here<br>
            <input type="radio" name="from" id="dist" value="zipcode"
                   onclick="document.getElementById('zipcode').required = true; document.getElementById('zipcode').readOnly = false"
                   <?php if(!isset($_GET['nearby'])) echo 'disabled'; ?>
                   <?php if(isset($_GET['nearby']) && isset($_GET['from']) && $_GET['from'] == 'zipcode') {echo 'checked';} ?>><input type="text" name="zipcode" id="zipcode" placeholder="zip code"
            value="<?php if(isset($_GET['nearby']) && $_GET['from'] == 'zipcode' && isset($_GET['zipcode'])) {echo $_GET['zipcode'];}?>"
            >
        </p>
        <input type="hidden" name="zip" id="zip" value="">
        <input type="hidden" name="ItemId" id="itemId" value="">
        <button type="submit" value="Search" name="search" id="search-button" disabled>Search</button>
        <input type="button" value="Clear" name="clear" id="clear-button"
               onclick="clearFormAndResultArea()">
    </form>
</div>

<div id="product-wrapper">
    <table id="product-results-table">
    </table>
    <table id="product-details-table">
    </table>
</div>

<div class="seller-msg">
    <center>
        <span id="open-seller-message">click to show seller message</span>
    </center>
    <center>
        <a href="#/" id="down-arrow-1"
           onclick="showSellerMessage(); if(document.getElementById('seller-message-content') !== null) { document.getElementById('seller-message-content').style.height=(document.getElementById('seller-message-content').contentWindow.document.body.scrollHeight + 50)+'px' ;}">
            <img src="http://csci571.com/hw/hw6/images/arrow_down.png" class="arrow-style">
        </a>
    </center>
    <center>
        <span id="close-seller-message">click to hide seller message</span>
    </center>
    <center>
        <a href="#/" id="up-arrow-1" onclick="hideSellerMessage()">
            <img src="http://csci571.com/hw/hw6/images/arrow_up.png" class="arrow-style">
        </a>
    </center>
</div>


<div id="seller-message-div">
    <iframe srcdoc="" id="seller-message-content" scrolling="no" frameborder="0" security="restricted"></iframe>
</div>


<div class="similar-items">
    <center>
        <span id="open-similar-items">click to show similar items</span>
    </center>
    <center>
        <a href="#/" id="down-arrow-2" onclick="showSimilarItems()">
            <img src="http://csci571.com/hw/hw6/images/arrow_down.png" class="arrow-style">
        </a>
    </center>
    <center>
        <span id="close-similar-items">click to hide similar items</span>
    </center>
    <center>
        <a href="#/" id="up-arrow-2" onclick="hideSimilarItems()">
            <img src="http://csci571.com/hw/hw6/images/arrow_up.png" class="arrow-style">
        </a>
    </center>
</div>

<div id="similar-items-div">

</div>
<script>

    function setDefaultSearchProperties(caller) {
        if (document.getElementById('nearby-search').checked) {
            document.getElementById('distance').disabled = false;
            document.getElementById('here').disabled = false;
            document.getElementById('dist').disabled = false;
            document.getElementById('zipcode').disabled = false;
            document.getElementById('zipcode').readOnly = true;
        }
        else {
            document.getElementById('distance').disabled = true;
            document.getElementById('here').checked = true;
            document.getElementById('here').disabled = true;
            document.getElementById('dist').disabled = true;
            document.getElementById('distance').value = '10';
            document.getElementById('zipcode').disabled = true;
            document.getElementById('zipcode').value = '';
        }
    }

    function clearFormAndResultArea() {
        document.getElementById('keyword').value = '';
        document.getElementById('category').selectedIndex = 0;
        document.getElementById('new').checked = false;
        document.getElementById('used').checked = false;
        document.getElementById('unspecified').checked = false;
        document.getElementById('local').checked = false;
        document.getElementById('free').checked = false;
        document.getElementById('nearby-search').checked = false;
        document.getElementById('distance').disabled = true;
        document.getElementById('distance').placeholder = '10';
        document.getElementById('distance').value = '';
        document.getElementById('here').checked = true;
        document.getElementById('here').disabled = true;
        document.getElementById('dist').disabled = true;
        document.getElementById('zipcode').disabled = true;
        document.getElementById('zipcode').value = '';
        document.getElementById('product-wrapper').innerHTML = '';
        document.getElementById('open-seller-message').style.display = 'none';
        document.getElementById('down-arrow-1').style.display = 'none';
        document.getElementById('seller-message-div').style.display = 'none';
        document.getElementById('close-seller-message').style.display = 'none';
        document.getElementById('up-arrow-1').style.display = 'none';
        document.getElementById('up-arrow-2').style.display = 'none';
        document.getElementById('open-similar-items').style.display = 'none';
        document.getElementById('close-similar-items').style.display = 'none';
        document.getElementById('similar-items-div').style.display = 'none';
        if (document.getElementById('similar-items') !== null) {
            document.getElementById('similar-items').style.display = 'none';
        }
        document.getElementById('down-arrow-2').style.display = 'none';
    }

    function getGeoLocationFromIPAPI(url, success, err) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', url, false);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (success) {
                    success(JSON.parse(xhr.responseText));
                } else {
                    err(xhr);
                }
            }
        };
        xhr.send();
    }

    function getProductDetails(itemId) {
        document.getElementById('itemId').value = itemId;
        let productForm = document.getElementById('myform');
        // console.log(productForm);
        productForm.submit();
    }

    function showSellerMessage() {
        document.getElementById('open-seller-message').style.display = 'none';
        document.getElementById('close-seller-message').style.display = 'block';
        document.getElementById('down-arrow-1').style.display = 'none';
        document.getElementById('up-arrow-1').style.display = 'block';
        document.getElementById('seller-message-div').style.display = 'block';
        // To hide similar items, if being shown
        hideSimilarItems();
    }

    function hideSellerMessage() {
        document.getElementById('close-seller-message').style.display = 'none';
        document.getElementById('open-seller-message').style.display = 'block';
        document.getElementById('seller-message-div').style.display = 'none';
        document.getElementById('up-arrow-1').style.display = 'none';
        document.getElementById('down-arrow-1').style.display = 'block';
    }

    function showSimilarItems() {
        document.getElementById('open-similar-items').style.display = 'none';
        document.getElementById('close-similar-items').style.display = 'block';
        document.getElementById('down-arrow-2').style.display = 'none';
        document.getElementById('up-arrow-2').style.display = 'block';
        document.getElementById('similar-items-div').style.display = 'block';
        // Hide Seller message if enabled
        hideSellerMessage();

    }

    function hideSimilarItems() {
        document.getElementById('close-similar-items').style.display = 'none';
        document.getElementById('open-similar-items').style.display = 'block';
        document.getElementById('up-arrow-2').style.display = 'none';
        document.getElementById('down-arrow-2').style.display = 'block';
        document.getElementById('similar-items-div').style.display = 'none';
    }

    function render(idOfItem) {
        document.getElementById('itemId').value = idOfItem;
        document.getElementById('myform').submit();
    }

    window.onload = function executeOnPageLoad() {
        getGeoLocationFromIPAPI('http://ip-api.com/json',
            function (resp) {
                let zip = resp['zip'];
                // console.log(zip);
                // console.log(resp);
                // console.log(document.getElementById('zip'));
                document.getElementById('zip').value = resp['zip'];
                document.getElementById('search-button').removeAttribute('disabled');
            },
            function (err) {
                console.error(err);
            });

        /* Construct Results Table */
        let productResultsTableHTML = "";
        let productResultsRow = "";
        let index = "";
        let photo = "";
        let productName = "";
        let price = "";
        let zipcodeFromAPI = "";
        let cond = "";
        let ship = "";
        let jsonEncodedString = <?php $productDetails = file_get_contents($findingApiUrl); echo json_encode($productDetails) ?>;
        // console.log(jsonEncodedString);
        if (jsonEncodedString !== undefined) {
            let productDetailsJSON = JSON.parse(jsonEncodedString);
            if (productDetailsJSON["findItemsAdvancedResponse"][0]["ack"][0] === "Success") {

                let len;
                len = productDetailsJSON["findItemsAdvancedResponse"][0]["searchResult"][0]["@count"];
                // console.log("Length" + len);
                if (len > 0) {
                    productResultsTableHTML += '<tr>\
										<th id="col-label-1">Index</th>\
										<th id="col-label-2">Photo</th>\
										<th id="col-label-3">Name</th>\
										<th id="col-label-4">Price</th>\
										<th id="col-label-5">Zip code</th>\
										<th id="col-label-6">Condition</th>\
										<th id="col-label-7">Shipping Option</th>\
									</tr>';

                    for (let i = 0; i < len; i++) {

                        if (productDetailsJSON["findItemsAdvancedResponse"][0]["searchResult"] !== undefined && productDetailsJSON["findItemsAdvancedResponse"][0]["searchResult"][0]["item"] !== undefined) {
                            let item = productDetailsJSON["findItemsAdvancedResponse"][0]["searchResult"][0]["item"];
                            let itemID = "";
                            if (item[i]["itemId"] !== undefined) {
                                index = i + 1;
                                itemID = item[i]["itemId"][0];
                                // console.log(itemID);
                            }

                            if (item[i]["galleryURL"] !== undefined) {
                                photo = item[i]["galleryURL"][0];
                                // console.log(photo);
                            }

                            if (item[i]["title"] !== undefined) {
                                productName = item[i]["title"];
                                // console.log(productName);
                            }

                            if (item[i]["sellingStatus"] !== undefined) {
                                // console.log(item[i]["sellingStatus"][0]["convertedCurrentPrice"][0]);
                                if (item[i]["sellingStatus"][0]["currentPrice"] !== undefined) {
                                    price = item[i]["sellingStatus"][0]["currentPrice"][0]["__value__"];
                                    // console.log(price);
                                }
                            }

                            if (item[i]["postalCode"] !== undefined) {
                                zipcodeFromAPI = item[i]["postalCode"][0];
                                // console.log(zipcodeFromAPI);
                            } else {
                                zipcodeFromAPI = "N/A";
                                // console.log(zipcodeFromAPI);
                            }

                            if (item[i]["condition"] !== undefined) {
                                cond = item[i]["condition"][0]["conditionDisplayName"][0];
                                // console.log(cond);
                            } else {
                                cond = "N/A";
                            }

                            if (item[i]["shippingInfo"] !== undefined) {
                                if (item[i]["shippingInfo"][0] !== undefined && item[i]["shippingInfo"][0]["shippingServiceCost"] !== undefined && item[i]["shippingInfo"][0]["shippingServiceCost"][0] !== undefined) {
                                    if (item[i]["shippingInfo"][0]["shippingServiceCost"][0]["__value__"] === "0.0") {
                                        ship = "Free Shipping";
                                        // console.log(ship);
                                    } else {
                                        ship = '$' + item[i]["shippingInfo"][0]["shippingServiceCost"][0]["__value__"];
                                        // console.log(ship);
                                    }
                                }
                            } else {
                                ship = "N/A";
                            }

                            productResultsRow = '<tr>\
                        <td>' + index + '</td>\
                        <td>' + '<img src="' + photo + '" class="image">' + '</td> \
                        <td>' + '<a href="#/" onclick="getProductDetails(' + itemID + ');">' + productName + '</a>' + '</td>\
                        <td>' + '$' + price + '</td>\
                        <td>' + zipcodeFromAPI + '</td>\
                        <td>' + cond + '</td>\
                        <td>' + ship + '</td>\
                        </tr>';

                            // console.log(productResultsRow);
                            productResultsTableHTML += productResultsRow;
                        }
                    }


                    document.getElementById('product-results-table').innerHTML = productResultsTableHTML;
                    // console.log(Object.keys(productDetailsJSON.findItemsAdvancedResponse));
                }
            } else if (productDetailsJSON["findItemsAdvancedResponse"][0]["ack"][0] === "Failure") {
                // console.log(productDetailsJSON["findItemsAdvancedResponse"][0]["errorMessage"][0]["error"][0]["message"][0]);
                if (productDetailsJSON["findItemsAdvancedResponse"][0]["errorMessage"][0]["error"][0]["message"] !== undefined) {
                    // console.log(productDetailsJSON["findItemsAdvancedResponse"][0]["errorMessage"][0]["message"]);
                    if (productDetailsJSON["findItemsAdvancedResponse"][0]["errorMessage"][0]["error"][0]["message"][0].includes("Invalid postal code")) {
                        document.getElementById('product-results-table').innerHTML = '<tr><td style="background-color:lightgray;"><center>Zipcode is invalid</center></td></tr>';
                    }
                }
            }

            if (productDetailsJSON["findItemsAdvancedResponse"][0]["ack"][0] === "Success" && productDetailsJSON["findItemsAdvancedResponse"][0]["searchResult"][0]["@count"] === "0") {
                document.getElementById('product-results-table').innerHTML = '<tr><td style="background-color:lightgray;"><center>No records have been found</center></td></tr>';
            }

        }

        // Initialize all values needed for Product Details Table
        let picture = "";
        let title = "";
        let subTitle = "";
        let cost = "";
        let loc = "";
        let seller = "";
        let retPolicy = "";
        let itemSpec = [];

        let productDetails = "";

        let productDetailsJSON = <?php $prod = file_get_contents($productDetailsURL); echo json_encode($prod) ?>;
        let parsedJSON = "";
        let status = "";
        if (productDetailsJSON !== undefined) {
            parsedJSON = JSON.parse(productDetailsJSON);
            status = parsedJSON["Ack"];
            // console.log(parsedJSON);
            // console.log(status);
            if (status === "Success") {
                let len = "";
                if (parsedJSON["Item"] !== undefined) {
                    if (parsedJSON["Item"]["PictureURL"] !== undefined) {
                        picture = parsedJSON["Item"]["PictureURL"][0];
                        // if(picture === undefined) {
                        //     picture = "Product Image Not Available";
                        // }
                        // console.log(picture);
                    }

                    if (parsedJSON["Item"]["Title"] !== undefined) {
                        title = parsedJSON["Item"]["Title"];
                        // console.log(title);
                    }

                    if (parsedJSON["Item"]["Subtitle"] !== undefined) {
                        subTitle = parsedJSON["Item"]["Subtitle"];
                        // console.log("Subtitle: " + subTitle);
                    }

                    if (parsedJSON["Item"]["CurrentPrice"] !== undefined) {
                        cost = parsedJSON["Item"]["CurrentPrice"]["Value"];
                        // console.log(cost);
                    }

                    if (parsedJSON["Item"]["Location"] !== undefined) {
                        loc = parsedJSON["Item"]["Location"];
                        if (parsedJSON["Item"]["PostalCode"] !== undefined) {
                            loc += ', ' + parsedJSON["Item"]["PostalCode"];
                        }
                        // console.log(loc);
                    }

                    if (parsedJSON["Item"]["Seller"] !== undefined) {
                        seller = parsedJSON["Item"]["Seller"]["UserID"];
                        // console.log(seller);
                    }

                    if (parsedJSON["Item"]["ReturnPolicy"] !== undefined) {
                        if (parsedJSON["Item"]["ReturnPolicy"]["ReturnsAccepted"] !== undefined) {
                            retPolicy = parsedJSON["Item"]["ReturnPolicy"]["ReturnsAccepted"];
                            if (parsedJSON["Item"]["ReturnPolicy"]["ReturnsWithin"] !== undefined) {
                                retPolicy += ' within ' + parsedJSON["Item"]["ReturnPolicy"]["ReturnsWithin"];
                            }
                        }
                    }

                    if (parsedJSON["Item"]["ItemSpecifics"] !== undefined) {
                        if (parsedJSON["Item"]["ItemSpecifics"]["NameValueList"] !== undefined) {
                            // console.log(parsedJSON["Item"]["ItemSpecifics"]["NameValueList"]);
                            let a = parsedJSON["Item"]["ItemSpecifics"]["NameValueList"];
                            //     for(let i = 0; i < a.length; i++) {
                            //         console.log(a[i]);
                            //         itemSpec[a["Name"]] = a["Value"];
                            //     }
                            //     console.log(itemSpec);
                            // }
                            // console.log(a);
                            for (let i = 0; i < a.length; i++) {
                                let name, value;
                                if (a[i]["Name"] !== undefined) {
                                    name = a[i]["Name"];
                                }
                                if (a[i]["Value"] !== undefined && a[i]["Value"][0] !== undefined) {
                                    value = a[i]["Value"][0];
                                }
                                // console.log(name + " " + value);
                                itemSpec.push({"Name": name, "Value": value});
                            }
                            // console.log(itemSpec);
                        }
                    }

                    productDetails += '<caption style="caption-side: top; text-align: center; font-weight: bolder; font-size: 33px;">Item Details</caption>';
                    if (picture !== undefined)
                        productDetails += '<tr><td><b>Photo</b></td><td>' + '<img src="' + picture + '" style="max-height: 275px;"></td></tr>';
                    else
                        productDetails += '<tr><td><b>Photo</b></td><td> Product Photo Not Found' + '</td></tr>';
                    if (title !== "")
                        productDetails += '<tr><td><b>Title</b></td><td>' + title + '</td></tr>';
                    if (subTitle !== "")
                        productDetails += '<tr><td><b>Subtitle</b></td><td>' + subTitle + '</td></tr>';
                    if (cost !== "")
                        productDetails += '<tr><td><b>Price</b></td><td>' + cost + ' USD</td></tr>';
                    if (loc !== "")
                        productDetails += '<tr><td><b>Location</b></td><td>' + loc + '</td></tr>';
                    if (seller !== "")
                        productDetails += '<tr><td><b>Seller</b></td><td>' + seller + '</td></tr>';
                    if (retPolicy !== "")
                        productDetails += '<tr><td><b>Return Policy (US)</b></td><td>' + retPolicy + '</td></tr>';

                    for (let i = 0; i < itemSpec.length; i++) {
                        productDetails += '<tr><td><b>' + itemSpec[i]["Name"] + '</b></td><td>' + itemSpec[i]["Value"];
                    }
                    document.getElementById('product-results-table').style.display = "none";
                    document.getElementById('product-details-table').innerHTML = productDetails;
                    document.getElementById('open-seller-message').style.display = 'block';
                    document.getElementById('down-arrow-1').style.display = 'block';
                    if (parsedJSON["Item"]["Description"] !== "") {
                        document.getElementById('seller-message-content').srcdoc = parsedJSON["Item"]["Description"];
                    } else {
                        document.getElementById('seller-message-div').innerHTML = "<div style='text-align: center; font-weight: bold; background-color: #dddddd; font-size: 18px; width: 85%; margin: 30px auto auto;'>No Seller Message found.</div>";
                    }
                    document.getElementById('open-similar-items').style.display = 'block';
                    document.getElementById('down-arrow-2').style.display = 'block';
                }
            }
            else if(status === 'Failure') {
                if(parsedJSON["Errors"] !== undefined) {
                    if(parsedJSON["Errors"][0] !== undefined) {
                        if(parsedJSON["Errors"][0]["ShortMessage"].includes("Invalid item ID")) {
                            alert(parsedJSON["Errors"][0]["ShortMessage"]);
                            document.getElementById('product-wrapper').style.display = 'none';
                        }
                    }
                }
            }
        }
        // renderTable();
        let imageTitle = "";
        let imageURL = "";
        let itemURL = "";
        let priceOfItem = "";
        let similarItemsRow = "<ul id=\"horizontal\">";
        let similarItemsJSON = <?php $items = file_get_contents($similarItemsURL); echo json_encode($items) ?>;
        let idOfItem = "";
        if (similarItemsJSON !== undefined) {
            let parsedItemsJSON = JSON.parse(similarItemsJSON);
            // console.log(parsedItemsJSON);
            // getSimilarItemDetails(parsedItemsJSON);
            if (parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"].length > 0) {
                for (let i = 0; i < parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"].length; i++) {
                    if (parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["title"] !== undefined) {
                        imageTitle = parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["title"];
                    }
                    if (parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["viewItemURL"] !== undefined) {
                        itemURL = parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["viewItemURL"];
                    }
                    if (parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["imageURL"] !== undefined) {
                        imageURL = parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["imageURL"];
                    }
                    if (parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["currentPrice"] !== undefined && parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["currentPrice"]["__value__"] !== undefined) {
                        priceOfItem = parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["currentPrice"]["__value__"];
                    } else if (parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["buyItNowPrice"] !== undefined && parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["buyItNowPrice"]["__value__"] !== undefined) {
                        priceOfItem = parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["buyItNowPrice"]["__value__"];
                    }

                    if (parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["itemId"] !== undefined) {
                        idOfItem = parsedItemsJSON["getSimilarItemsResponse"]["itemRecommendations"]["item"][i]["itemId"];
                        // console.log(idOfItem);
                    }

                    if(imageURL) {
                        similarItemsRow += '<li><div style="text-align:center;"><img src="' + imageURL + '>"</div>';
                    }
                    else {
                        similarItemsRow += '<li><div style="text-align:center;">Image Not Found</div>';
                    }
                    similarItemsRow += '<div style="width:250px; text-align:center; padding-top:5px; padding-right: 10px;"><a href="#/" onclick="render(' + idOfItem + ');">' + '<span id="image-title">' + imageTitle + '</a></span></div>';
                    similarItemsRow += '<div style="text-align:center; padding-top:5px;"><b>' + "$" + priceOfItem + '</b></div></li>';
                }
                similarItemsRow += '</ul>';
                document.getElementById('similar-items-div').innerHTML = similarItemsRow;
            } else {
                document.getElementById('similar-items-div').innerHTML = "<b>No Similar Item Found.</b>";
            }
        }
    }
</script>

</body>
</html>