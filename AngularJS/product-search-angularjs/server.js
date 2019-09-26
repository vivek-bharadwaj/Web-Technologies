let express = require("express");
let request = require("request");
let app = express();

app.use(express.static("./"));

app.get("/getProducts", (req, res) => {
  console.log(req.query);
  let findingApiUrl =
      "http://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.0&SECURITY-APPNAME=<APP_ID>&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&paginationInput.entriesPerPage=50&keywords=";
  findingApiUrl +=
      req.query.Keyword;

  let categoryId;
  switch (req.query.Category) {
    case "Art": categoryId = "550";
      break;
    case "Baby": categoryId = "2984";
      break;
    case "Books": categoryId = "267";
      break;
    case "Clothing, Shoes & Accessories": categoryId = "11450";
      break;
    case "Computers/Tablets & Networking": categoryId = "58058";
      break;
    case "Health & Beauty": categoryId = "26395";
      break;
    case "Music": categoryId = "11233";
      break;
    case "Video Games & Consoles": categoryId = "1249";
      break;
  }
  if(categoryId) {
    console.log(categoryId);
    findingApiUrl += "&categoryId=" + categoryId;
  }

  findingApiUrl +=
      "&buyerPostalCode=" +
      req.query.Zipcode +
      "&itemFilter(0).name=MaxDistance&itemFilter(0).value=" +
      req.query.Distance;

  let i = 1;
  let ship = JSON.parse(req.query.Shipping);
  let s_l = Object.keys(ship).length;
  if (s_l > 0) {
    let keys = Object.keys(ship);
    for (let key of keys) {
      if (key === "Free") {
        findingApiUrl +=
            "&itemFilter(" +
            i +
            ").name=FreeShippingOnly&itemFilter(" +
            i +
            ").value=true";
        i++;
      }
      if (key === "Local") {
        findingApiUrl +=
            "&itemFilter(" +
            i +
            ").name=LocalPickupOnly&itemFilter(" +
            i +
            ").value=true";
        i++;
      }
    }
  }
  findingApiUrl +=
      "&itemFilter(" +
      i +
      ").name=HideDuplicateItems&itemFilter(" +
      i +
      ").value=true";
  i++;

  let cond = JSON.parse(req.query.Condition);
  let c_l = Object.keys(cond).length;

  // // console.log(cond, typeof cond);
  let j = 0;
  if (c_l > 0) {
    let keys = Object.keys(cond);
    for (let key of keys) {
      if (key === "New") {
        findingApiUrl +=
            "&itemFilter(" +
            i +
            ").name=Condition&itemFilter(" +
            i +
            ").value(" +
            j +
            ")=New";
        j++;
      }
      if (key === "Used") {
        findingApiUrl +=
            "&itemFilter(" +
            i +
            ").name=Condition&itemFilter(" +
            i +
            ").value(" +
            j +
            ")=Used";
        j++;
      }
      if (key === "Unspecified") {
        findingApiUrl +=
            "&itemFilter(" +
            i +
            ").name=Condition&itemFilter(" +
            i +
            ").value(" +
            j +
            ")=Unspecified";
        j++;
      }
    }
  }
  findingApiUrl += " &outputSelector(0)=SellerInfo&outputSelector(1)=StoreInfo";
  console.log(findingApiUrl);
  // // console.log(Object.keys(cond));

  request.get(
      {
        url: findingApiUrl,
        json: true,
        headers: { "User-Agent": "request" }
      },
      (err, response, data) => {
        if (err) {
          // console.log("Error", err);
        } else if (response.statusCode !== 200) {
          // console.log("Status: ", response.statusCode);
        } else {
          res.setHeader("content-type", "application/json");
          res.json(data);
        }
      }
  );
});

app.get("/autoCompleteZipCode/:zip", (req, res) => {
  // console.log(req.params.zip);
  request.get(
      {
        url:
            "http://api.geonames.org/postalCodeSearchJSON?postalcode_startsWith=" +
            req.params.zip +
            "&username=<user_name>&country=US&maxRows=5"
      },
      (err, response, data) => {
        // // console.log(url);
        let zipCodesData = JSON.parse(data);
        // console.log(zipCodesData.postalCodes);
        let zipCodes = [];
        for (let i = 0; i < zipCodesData.postalCodes.length; i++) {
          zipCodes.push(zipCodesData.postalCodes[i].postalCode);
        }
        // console.log(zipCodes);
        res.send(zipCodes);
      }
  );
});

app.get("/productDetails/:itemId", (req, res) => {
  // // console.log(req.params);
  request.get(
      {
        url:
            "http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=JSON&appid=<APP_ID>&siteid=0&version=967&ItemID=" +
            req.params.itemId +
            "&IncludeSelector=Description,Details,ItemSpecifics"
      },
      (err, response, data) => {
        if (err) {
          // console.log(err);
        } else if (response.statusCode !== 200) {
          // console.log("Status: ", response.statusCode);
        } else {
          let parsedData = JSON.parse(data);
          res.setHeader("content-type", "application/json");
          res.json(parsedData);
        }
      }
  );
});

app.get("/productPhotos/:itemTitle", (req, res) => {
  // console.log("Google Custom API");
  // console.log(req.params);
  request.get(
      {
        url:
            "https://www.googleapis.com/customsearch/v1?q=" +
            encodeURI(req.params.itemTitle) +
            "&cx=017639350353132696891:_apf7b96xvo&imgSize=huge&imgType=news&num=8&searchType=image&key=<key>"
      },
      (err, response, data) => {
        if (err) {
          // console.log("Error: ", err);
        }
        // console.log(data);
        let parsedJSON = JSON.parse(data);
        res.setHeader("content-type", "application/json");
        res.json(parsedJSON);
      }
  );
});

app.get("/similarItems/:itemId", (req, res) => {
  // console.log("Similar Items eBay Merchandising API");
  // console.log(req.params);
  request.get(
      {
        url:
            "http://svcs.ebay.com/MerchandisingService?OPERATION-NAME=getSimilarItems&SERVICE-NAME=MerchandisingService&SERVICE-VERSION=1.1.0&CONSUMER-ID=<APP_ID>&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&itemId=" +
            req.params.itemId +
            "&maxResults=20"
      },
      (err, response, data) => {
        if (err) {
          // console.log(err);
        }
        // console.log(data);
        let similarItemsJson = JSON.parse(data);
        res.setHeader("content-type", "application/json");
        res.json(similarItemsJson);
      }
  );
});
//
// let server = app.listen(process.env.PORT);
let server = app.listen(8081, function() {
  let port = server.address().port;
  console.log("Listening on port %s", port);
});
