let load_more_count = 1;

document.addEventListener("DOMContentLoaded", function (event) {
  fetchCaseData(load_more_count);
  if (event.target instanceof Element) {
    let target = event.target.closest(".ajax-load-more");
    let loadMoreButton = target.querySelector(".bb_ajax-load-more-btn");
    if (loadMoreButton) {
      // Get the current offset from data-offset
      let currentOffsetLoad = parseInt(
        loadMoreButton.getAttribute("data-offset"),
        10
      );
      if (currentOffsetLoad == 1) {
        loadMoreButton.style.display = "none";
      }
    }
  }
});

document.addEventListener("click", function (event) {
  let target = event.target.closest(".ajax-load-more");
  if (target) {
    event.preventDefault();
    let loadMoreButton = target.querySelector(".bb_ajax-load-more-btn");
    if (loadMoreButton) {
      // Get the current offset from data-offset
      let currentOffset = parseInt(
        loadMoreButton.getAttribute("data-offset"),
        10
      );
      fetchCaseData(currentOffset);
      // Increment the count by 1
      currentOffset += 1;
      loadMoreButton.setAttribute("data-offset", currentOffset);
    }
  }
});

function fetchCaseData(load_more_count) {
  const elementCase = document.querySelector(".bb-patient-box");
  if (elementCase) {
    elementCase.insertAdjacentHTML(
      "beforeend",
      `<img id="bb_f_gif_sidebar" src="${bb_plugin_data.heartrunning}" alt="Loading...">`
    );
  }
  const element = document.querySelector(".bb-content-boxes");
  if (element) {
    element.insertAdjacentHTML(
      "beforeend",
      `<img id="bb_f_gif_sidebar" src="${bb_plugin_data.heartrunning}" alt="Loading...">`
    );
  }
  const loadMoreButtonEl = document.querySelector(".bb_ajax-load-more-btn");
  if (loadMoreButtonEl) {
    loadMoreButtonEl.innerHTML = `<img id="bb_ajax-load-more-btn" src="${bb_plugin_data.heartrunning}" alt="Loading...">`;
  }
  // const element_apply = document.querySelector('.apply_bb_filter');
  // if (element_apply) element_apply.innerHTML = `<img id="apply_bb_filter" src="${bb_plugin_data.heartrunning}" alt="Loading...">`;
  var elementId = "";
  const pathSegments = window.location.pathname.split("/").filter(Boolean);
  const secondPart = pathSegments[0] || "";
  const thirdPart = pathSegments[1] || "";
  var forthPart = pathSegments[2] || "";
  const fifthPart = pathSegments[3] || "";
  //alert(forthPart);
  let favorites;
  if(thirdPart == "favorites") {
    favorites = thirdPart; 
  }

  const count = load_more_count;
  const targetHref = `/${secondPart}/${thirdPart}/`;
  const targetElement = document.querySelector(`a[href="${targetHref}"]`);

  if (targetElement) {
    elementId = targetElement.id;
    var apiToken = targetElement.getAttribute("data-api-token");
    var websitePropertyId = targetElement.getAttribute(
      "data-website-property-id"
    );
    var textOnly = Array.from(targetElement.childNodes)
      .filter((node) => node.nodeType === Node.TEXT_NODE)
      .map((node) => node.textContent.trim())
      .join(" ");

    // Change color and opacity
    targetElement.style.color = "#000";
    targetElement.style.opacity = "1";

    // Open the corresponding accordion panel
    let accordionButton =
      targetElement.closest(".bb-panel")?.previousElementSibling;
    if (accordionButton && accordionButton.classList.contains("bb-accordion")) {
      accordionButton.classList.add("active");

      let panel = accordionButton.nextElementSibling;
      if (panel && panel.classList.contains("bb-panel")) {
        panel.style.display = "block";
        panel.style.maxHeight = panel.scrollHeight + "px";
      }
    }
  }
  let dynamicFilterBB = document.querySelectorAll(
    '.bb-dynamic-filter input[type="checkbox"]:checked'
  );
  let staticFilterBB = document.querySelectorAll(
    '.bb-static-filter input[type="checkbox"]:checked'
  );
  // Loop through each checkbox
  let staticFilter = "";
  let staticFilterCombine = {};
  let dynamicFilter = "";
  let dynamicFilterCombine = {};
  staticFilterBB.forEach((checkbox) => {
    let dataKey = checkbox.getAttribute("data-key");
    let dataValue = checkbox.getAttribute("data-value");
    staticFilter += `&${dataKey}=${dataValue}`;
    dataValue=isNaN(parseInt(dataValue))?`"${dataValue}"`:parseInt(dataValue);
    staticFilterCombine[dataKey]=dataValue;
  });

  dynamicFilterBB.forEach((checkbox) => {
    let dataKey = checkbox.getAttribute("data-key");
    let dataValue = checkbox.getAttribute("data-value");
    dataKey = dataKey.replace(/\s+/g, "|||");
    dynamicFilter += `&${dataKey}=${dataValue}`;
    dataValue=isNaN(parseInt(dataValue))?`${dataValue}`:parseInt(dataValue);
    dynamicFilterCombine[dataKey]=dataValue;
  });
  let updatedCaseId = "";
  let seoSuffixUrl = "";
  if(forthPart.includes("bb-case")) {
    updatedCaseId = forthPart.split("-").pop()
  } else {
    seoSuffixUrl = forthPart
  }

  const data = {
    action: "bb_case_api",
    count: count,
    pageSlug: secondPart,
    favorites: favorites,
    procedureId: elementId,
    apiToken: apiToken,
    websitePropertyId: websitePropertyId,
    caseId: updatedCaseId,
    seoSuffixUrl: seoSuffixUrl,
    staticFilter: staticFilter,
    dynamicFilter: dynamicFilter,
    dynamicFilterCombine: Object.keys(dynamicFilterCombine).length?JSON.stringify(dynamicFilterCombine):0,
    ...staticFilterCombine
  };

  fetch(bb_plugin_data.ajaxurl, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(data).toString(),
  })
    .then((response) => response.json())
    .then((data) => { 
      try {
        let heartImage;
        let sidebarApi; 
        if(data.data.sidebar_api) { 
          sidebarApi = JSON.parse(JSON.parse(data.data.sidebar_api)).data?.reduce((procedures, b) => {
            procedures.push(...b.procedures);
            return procedures;
          }, []); 
        }

        if(data.data.bragbook_favorite.length > 0) {
          var fav_data = data.data.bragbook_favorite;
          let element_fav_count = document.getElementById("bb_favorite_caseIds_count");
          element_fav_count.textContent = "(" + `${fav_data.length}` + ")";
        }
        if (data.data && data.data.case_set) {
          let caseSet = JSON.parse(data.data.case_set);
          if (forthPart == "") {
            var bb_case_count = (count - 1) * 10;
            let contentBox = document.querySelector(".bb-content-boxes");

            document.querySelector("#bb_f_gif_sidebar")?.remove();
            document.querySelector("#bb_ajax-load-more-btn")?.remove();
            //  document.querySelector('#apply_bb_filter')?.remove();
            if(thirdPart !== "favorites") {
              document.querySelector(".bb_ajax-load-more-btn").innerHTML =
              "Load More";
            }
            const applyBBButton = document.querySelector(".apply_bb_filter");
            if (applyBBButton) applyBBButton.innerHTML = `Apply`;
            let images = [];
            if(caseSet.data) {
              
              caseSet.data.forEach((caseItem, index) => {
                if (caseItem.photoSets && caseItem.photoSets.length > 0) {
                  let photoSet = caseItem.photoSets[0];
                  let imgSrc =
                    photoSet.highResPostProcessedImageLocation ||
                    photoSet.postProcessedImageLocation ||
                    photoSet.originalBeforeLocation;
                  let imgAlt = photoSet.seoAltText + " - angle " + (index + 1) || "Procedure Image";
                  let caseItemId = caseItem.id;
                  let caseDetails = caseItem.details || "";
                  caseItem.patientCount = ++bb_case_count;

                  console.log("caseItem::", caseItem);
                  
                  let caseId = "";
                  seoSuffixUrl = caseItem.caseDetails[0].seoSuffixUrl;
                  if(seoSuffixUrl) {
                    caseId = seoSuffixUrl; 
                  } else {
                    caseId = "bb-case-"+caseItemId;
                  }
                  let procedureUrl = `/${secondPart}/${thirdPart}/${caseId}/`;
                  
                  if(fav_data && fav_data.includes(caseId)) {
                    heartImage = bb_plugin_data.heartBordered;
                  }else {
                    heartImage = bb_plugin_data.heartRed;
                  }  
                  let imageObj = {
                    "@type": "ImageObject",
                    "name": thirdPart, 
                    "description": `Photo gallery of ${thirdPart} results showing before and after photos from different angles.`,
                    "url": `${targetHref}${caseItem.caseId}`,  
                    "thumbnailUrl": imgSrc
                };
                
                let proceduralName = "";
                if(caseItem.caseDetails[0].seoHeadline) {
                  proceduralName = caseItem.caseDetails[0].seoHeadline
                } else {
                  let titleWithoutDashes = thirdPart.replace(/-/g, ' ');
                  proceduralName = titleWithoutDashes + ': Patient ' + caseItem.patientCount;
                }
                
                // Add the image object to the images array
                images.push(imageObj);
                  let newContent = `
                              <div class="bb-content-box">
                                  <div class="bb-content-thumbnail">
                                      <a href="${procedureUrl}">
                                          <img src="${imgSrc}" alt="${imgAlt}">
                                      </a>
                                      <img class="bb-heart-icon bb-open-fav-modal" 
                                          data-case-id="${caseId}"
                                          data-bb_api_token="${apiToken}" 
                                          data-bb_website_id="${websitePropertyId}" 
                                          src="${heartImage}" 
                                          alt="heart">
                                  </div>
                                  <div class="bb-content-box-inner">
                                      <div class="bb-content-box-inner-left">
                                          <h2>${proceduralName}</h2>
                                          <p>${caseDetails}</p> 
                                      </div>
                                      <div class="bb-content-box-inner-right">
                                          <img class="bb-open-fav-modal" 
                                              data-case-id="${caseId}" 
                                              data-bb_api_token="${apiToken}" 
                                              data-bb_website_id="${websitePropertyId}" 
                                              src="${heartImage}" 
                                              alt="heart">
                                      </div>
                                  </div>
                                  <div class="bb-content-box-cta">
                                      <a class="view-more-btn" href="${procedureUrl}">
                                          View More
                                      </a>
                                  </div>
                              </div>
                          `;
  
                  contentBox.innerHTML += newContent;
                }
              });
                let schema = {
                    "@context": "https://schema.org",
                    "@type": "ImageGallery",
                    "name": `${thirdPart} Before & After Gallery`,
                    "description": `Review ${caseSet.data.length} ${thirdPart} before and after cases. Each case includes photos from multiple angles, along with details about the procedure.`,
                    "url": `/${secondPart}/${thirdPart}/`,  // Assuming `secondPart` and `thirdPart` are dynamically generated
                    "image": images,
                    "breadcrumb": {
                        "@type": "BreadcrumbList",
                        "itemListElement": [
                            {
                                "@type": "ListItem",
                                "position": 1,
                                "name": "Home",
                                "item": `/`
                            },
                            {
                                "@type": "ListItem",
                                "position": 2,
                                "name": `${thirdPart}`,
                                "item": `/${secondPart}/${thirdPart}/`
                            },
                            {
                                "@type": "ListItem",
                                "position": 3,
                                "name": `${thirdPart} Procedure`,
                                "item": `/${secondPart}/${thirdPart}/`
                            }
                        ]
                    }
                };
                // Inject the generated schema as JSON-LD into the HTML 
                let schemaJson = JSON.stringify(schema, null, 4); // Pretty-print the schema 
                let schemaScript = document.createElement('script');
                schemaScript.type = 'application/ld+json';
                schemaScript.innerHTML = schemaJson;
                document.head.appendChild(schemaScript);
              
            } else if (caseSet.favorites) {
              caseSet.favorites.forEach((caseItem) => {
                if (caseItem.cases[0].photoSets && caseItem.cases[0].photoSets.length > 0 && sidebarApi) {
                  if (caseItem && caseItem.cases[0].procedureIds.length > 0) {
                    const isCombine = sidebarApi[0].ids;
                    let slugName;
                    if(isCombine) {
                      const totalTokens=2;
                      for(let i=0;i<totalTokens;i++){
                        if(slugName) break;
                        slugName= sidebarApi.find(p=>p.ids[i]==caseItem.cases[0].procedureIds[0])?.slugName;
                      }
                    }else{
                      slugName= sidebarApi.find(p=>p.id==caseItem.cases[0].procedureIds[0])?.slugName;
                    }

                    let photoSet = caseItem.cases[0].photoSets[0];
                    let imgSrc =
                      photoSet.highResPostProcessedImageLocation ||
                      photoSet.postProcessedImageLocation ||
                      photoSet.originalBeforeLocation;
                    let imgAlt = photoSet.seoAltText || "Procedure Image";
                    let caseId = caseItem.cases[0].id;
                    let caseDetails = caseItem.cases[0].details || "";
                    caseItem.patientCount = ++bb_case_count;
                    let procedureUrl = `/${secondPart}/${slugName}/${caseId}/`;
                    
                    heartImage = bb_plugin_data.heartBordered;
                    
                    let newContent = `
                                <div class="bb-content-box">
                                    <div class="bb-content-thumbnail">
                                        <a href="${procedureUrl}">
                                            <img src="${imgSrc}" alt="${imgAlt}">
                                        </a>
                                        <img class="bb-heart-icon bb-open-fav-modal" 
                                            data-case-id="${caseId}"
                                            data-bb_api_token="${apiToken}" 
                                            data-bb_website_id="${websitePropertyId}" 
                                            src="${heartImage}" 
                                            alt="heart">
                                    </div>
                                    <div class="bb-content-box-inner">
                                        <div class="bb-content-box-inner-left">
                                            <h5>${thirdPart} : Patient ${caseItem.patientCount}</h5>
                                            <p>${caseDetails}</p> 
                                        </div>
                                        <div class="bb-content-box-inner-right">
                                            <img class="bb-open-fav-modal" 
                                                data-case-id="${caseId}" 
                                                data-bb_api_token="${apiToken}" 
                                                data-bb_website_id="${websitePropertyId}" 
                                                src="${heartImage}" 
                                                alt="heart">
                                        </div>
                                    </div>
                                    <div class="bb-content-box-cta">
                                        <a class="view-more-btn" href="${procedureUrl}">
                                            View More
                                        </a>
                                    </div>
                                </div>
                            `;
    
                    contentBox.innerHTML += newContent;
                  }
                }
              });
            }
            
          } else {
            let images_case = [];
            document.querySelector("#bb_f_gif_sidebar")?.remove();
            let patienLeftBox = document.querySelector(".bb-patient-left");
            if (patienLeftBox) {
              caseSet.data.forEach((caseItem) => {
                let caseId = caseItem.id;
                if (caseId == updatedCaseId) {
                  if (caseItem.photoSets && caseItem.photoSets.length > 0) {
                    caseItem.photoSets.forEach((value, itemIndex) => {
                      console.log('itemIndex: ',itemIndex);
                      let bb_new_image_value =
                        value.highResPostProcessedImageLocation ??
                        value.postProcessedImageLocation ??
                        value.originalBeforeLocation;

                      let imgElement = document.createElement("img");
                      imgElement.className =
                        "bbrag_gallery_image testing-image";
                      imgElement.src = bb_new_image_value;
                      imgElement.alt = value.seoAltText + " - angle " + (itemIndex +1) ?? "Procedure Image";
                      patienLeftBox.appendChild(imgElement);
                      let imageObjc = {
                        "@type": "ImageObject",
                        "name": thirdPart, 
                        "description": `Photo gallery of ${thirdPart} results showing before and after photos from different angles.`,
                        "url": `${targetHref}${caseItem.id}`,  
                        "thumbnailUrl": bb_new_image_value
                      };
          
                      // Add the image object to the images array
                      images_case.push(imageObjc); 
                    });
                    
                  }
                }
              });
            }
            let patientRightBox = document.querySelector(".bb-patient-right");
            if (patientRightBox) {
              caseSet.data.forEach((caseItem) => {
                let patientDetail = caseItem.details || "";
                let height = caseItem.height
                  ? `<li>HEIGHT: ${caseItem.height
                      .toString()
                      .toLowerCase()}</li>`
                  : "";
                let width = caseItem.weight
                  ? `<li>WEIGHT: ${caseItem.weight
                      .toString()
                      .toLowerCase()}</li>`
                  : "";
                let race = caseItem.ethnicity
                  ? `<li>RACE: ${caseItem.ethnicity.toLowerCase()}</li>`
                  : "";
                let gender = caseItem.gender
                  ? `<li>GENDER: ${caseItem.gender.toLowerCase()}</li>`
                  : "";
                let age = caseItem.age
                  ? `<li>AGE: ${caseItem.age.toString().toLowerCase()}</li>`
                  : "";
                let timeframe =
                  caseItem.after1Timeframe && caseItem.after1Unit
                    ? `<li>POST-OP PERIOD: ${caseItem.after1Timeframe
                        .toString()
                        .toLowerCase()} ${caseItem.after1Unit.toLowerCase()}</li>`
                    : "";
                let timeframe2 =
                  caseItem.after2Timeframe && caseItem.after2Unit
                    ? `<li>2nd AFTER: ${caseItem.after2Timeframe
                        .toString()
                        .toLowerCase()} ${caseItem.after2Unit.toLowerCase()}</li>`
                    : "";
                let revisionSurgery = caseItem.revisionSurgery
                  ? `<li>This case is a revision of a previous procedure.</li>`
                  : "";
                  if(fav_data?.includes(caseItem.id)) {
                    heartImage = bb_plugin_data.heartBordered;
                  }else {
                    heartImage = bb_plugin_data.heartRed;
                  } 
                  console.log("Case Details::::: ", caseItem); 

                  // Split the string by the delimiter ' - ' and get the second part
                  let title_suffix = document.title.split(' - ')[1];
                  
                  document.title = caseItem.caseDetails[0]?.seoPageTitle + " - " + title_suffix || document.title;
                
                let bb_right_data = `
                        <div class="bb-patient-row">
                            <h2>${caseItem.caseDetails[0]?.seoHeadline || textOnly}</h2>
                            <img class="bb-heart-icon bb-open-fav-modal" 
                                data-case-id="${forthPart}" 
                                data-bb_api_token="${apiToken}" 
                                data-bb_website_id="${websitePropertyId}" 
                                src="${heartImage}" alt="heart">
                        </div>
                        <ul>
                            ${height}
                            ${width}
                            ${race}
                            ${gender}
                            ${age}
                            ${timeframe}
                            ${timeframe2}
                            ${revisionSurgery}
                        </ul>
                        <p>${patientDetail}</p>
                         <div class="bb-patient-slides">
                          <ul id="pagination-list-${caseItem.id}" class="bb-pagination"></ul>
                        </div>
                    `;

                patientRightBox.innerHTML += bb_right_data;
                let paginationData = generatePagination(
                  caseItem.caseIds,
                  caseItem.id
                );
                renderPagination(paginationData, caseItem.id, targetHref);
              });
            }
            // Create schema object once after processing all cases
            let bb_case_url_title = 'Cosmetic Procedure'; // Adjust based on your actual dynamic data
            let bb_current_case_page_count = ''; //caseSet.data.length; // Adjust according to your data
            let default_and_seo_page_title = "Procedure Title"; // Set dynamically
            let procedure_description = "Detailed description of the procedure."; // Set dynamically
            let bb_gallery_page_title = "Gallery Page Title"; // Set dynamically
            let case_page_title = "Case Page Title"; // Set dynamically
            let bbrag_case_url = "/case-gallery"; // Set dynamically
            let bb_pro_cat_page = "/category-page"; // Set dynamically
            let targetHrefc = "/target-url/"; // Set dynamically
            let forthPartc = "case-id"; // Adjust accordingly
    
            let schema = {
              "@context": "https://schema.org",
              "@type": "ImageGallery",
              "name": `Before and After Gallery ${bb_case_url_title} : Patient ${bb_current_case_page_count}`,
              "description": `Photo gallery of ${bb_case_url_title} results showing before and after photos from different angles.`,
              "mainEntity": {
                  "@type": "MedicalProcedure",
                  "name": default_and_seo_page_title,
                  "description": procedure_description,
                  "procedureType": "CosmeticProcedure",
                  "medicalSpecialty": "PlasticSurgery"
              },
              "image": images_case,
              "breadcrumb": {
                  "@type": "BreadcrumbList",
                  "itemListElement": [
                      {
                          "@type": "ListItem",
                          "position": 1,
                          "name": "Home",
                          "item": "/"
                      },
                      {
                          "@type": "ListItem",
                          "position": 2,
                          "name": bb_gallery_page_title,
                          "item": `/`
                      },
                      {
                          "@type": "ListItem",
                          "position": 3,
                          "name": `Before and After ${case_page_title} Gallery`,
                          "item": bb_pro_cat_page
                      },
                      {
                          "@type": "ListItem",
                          "position": 4,
                          "name": default_and_seo_page_title,
                          "item": `/${bbrag_case_url}`
                      }
                  ]
              },
              "url": `/${bbrag_case_url}`
          };

          // Convert the schema object to a JSON string
          let schemaJson = JSON.stringify(schema, null, 4);  // Pretty-print the schema

          // Create the <script> tag for JSON-LD
          let schemaScript = document.createElement('script');
          schemaScript.type = 'application/ld+json';
          schemaScript.innerHTML = schemaJson;

          // Append the schema script to the <head> section
          document.head.appendChild(schemaScript);
          } 
        } else {
          console.error("Invalid response structure:", data);
        }
        if (data.data && data.data.filter_data) {
          if (
            document.querySelector(".bb-filter-content-inner") &&
            document.querySelector(".bb-filter-content-inner")
              .childElementCount === 0
          ) {
            let filterContent = document.querySelector(
              ".bb-filter-content-inner"
            );
            let filter_data = JSON.parse(data.data.filter_data);

            // Static filters (e.g., height, weight, etc.)
            if (filter_data.data.staticFilter) {
              for (let filterKey in filter_data.data.staticFilter) {
                let filterGroup = filter_data.data.staticFilter[filterKey];
                let filterHTML = `<div class="bb-filter-content-inner-wrapper">
                                        <button class="accordion">${filterKey} <img src="${bb_plugin_data.heartdown}" alt="down"></button>
                                        <div class="panel">
                                            <div class="bb-filter-select-wrapper">
                                                <div class="bb-input-box">`;

                filterGroup.forEach((option) => {
                  filterHTML += `<label class="bb-checkbox-container bb-static-filter" for="m-${filterKey}-${option.value}">
                                        ${option.label}
                                        <input type="checkbox" id="m-${filterKey}-${option.value}" name="${filterKey}" data-key="${filterKey}" data-value="${option.value}" onchange="handleDynamicCheckboxChange('${filterKey}','${option.value}')">
                                        <span class="checkmark"></span>
                                      </label>`;
                });

                filterHTML += `</div></div></div></div>`;
                filterContent.innerHTML += filterHTML;
              }
            }
            if (filter_data.data.dynamicFilter) {
              //if(filter_data.data.dynamicFilter.length > 0){
              var filterHTMLA = `<div class='advanced-filters'><span>Advanced Filters</span></div>`;
              // }
              for (let filterKey in filter_data.data.dynamicFilter) {
                let filterGroup = filter_data.data.dynamicFilter[filterKey];
                filterHTMLA += `<div class="bb-filter-content-inner-wrapper">
                                        <button class="accordion">${filterKey} <img src="${bb_plugin_data.heartdown}" alt="down"></button>
                                        <div class="panel">
                                            <div class="bb-filter-select-wrapper"><div class="bb-input-box">`;

                filterGroup.forEach((option) => {
                  let formattedKey = filterKey;
                  let formattedOption = option;
                  filterHTMLA += `<label class="bb-checkbox-container bb-dynamic-filter" for="${formattedKey}-${formattedOption}">
                                        ${option}
                                        <input type="checkbox" id="${formattedKey}-${formattedOption}" name="${formattedKey.replace(
                    /\s+/g,
                    ""
                  )}" data-key="${formattedKey}" data-value="${formattedOption}" onchange="handleDynamicCheckboxChange('${formattedKey.replace(
                    /\s+/g,
                    ""
                  )}','${formattedOption}')">
                                        <span class="checkmark"></span>
                                      </label>`;
                });

                filterHTMLA += `</div></div></div></div>`;
              }
              filterContent.innerHTML += filterHTMLA;
            }
            filterContent.innerHTML += `<div  id='apply_filter'>
                <button class="apply_bb_filter" onClick='applyFilterBB(${count}, "${secondPart}", "${elementId}", "${apiToken}", "${websitePropertyId}", "${forthPart}", "${thirdPart}")'>Apply</button> 
                </div>`;

            bb_advance_filter();
          }
        }
      } catch (err) {
        console.error("Error parsing case_set JSON:", err);
      }
    })
    .catch((error) => {
      console.error("Error during AJAX request:", error);
    });
}
function handleDynamicCheckboxChange(key, value) {
  const checkboxes = document.querySelectorAll(`input[name=${key}]`);

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", (event) => {
      // Uncheck all checkboxes before checking the current one
      checkboxes.forEach((cb) => {
        if (cb !== event.target) {
          cb.checked = false;
        }
      });
    });
  });
}

function applyFilterBB(
  count,
  secondPart,
  elementId,
  apiToken,
  websitePropertyId,
  forthPart,
  thirdPart
) {
  document.querySelector(".bb-content-boxes").innerHTML = "";
  document.querySelector(
    ".apply_bb_filter"
  ).innerHTML = `<img id="apply_bb_filter" src="${bb_plugin_data.heartrunning}" alt="Loading...">`;
  //document.querySelector(".apply_bb_filter").innerHTML = "";

  let dynamicFilterBB = document.querySelectorAll(
    '.bb-dynamic-filter input[type="checkbox"]:checked'
  );
  let staticFilterBB = document.querySelectorAll(
    '.bb-static-filter input[type="checkbox"]:checked'
  );
  // Loop through each checkbox
  let staticFilterCombine = {};
  let staticFilter = "";
  let dynamicFilter = "";
  // let dynamicFilterCombine = `"filters": {`;
  let dynamicFilterCombine = {};

  staticFilterBB.forEach((checkbox) => {
    let dataKey = checkbox.getAttribute("data-key");
    let dataValue = checkbox.getAttribute("data-value");
    staticFilter += `&${dataKey}=${dataValue}`;
    dataValue=isNaN(parseInt(dataValue))?`"${dataValue}"`:parseInt(dataValue);
    staticFilterCombine[dataKey]=dataValue;
  });


  
  dynamicFilterBB.forEach((checkbox) => {
    let dataKey = checkbox.getAttribute("data-key");
    let dataValue = checkbox.getAttribute("data-value");
    dataKey = dataKey.replace(/\s+/g, "|||");
    dynamicFilter += `&${dataKey}=${dataValue}`;
    dataValue=isNaN(parseInt(dataValue))?`${dataValue}`:parseInt(dataValue);
    dynamicFilterCombine[dataKey]=dataValue;
    // dynamicFilterCombine += `"${dataKey}":"${dataValue}",`;
  });
  // dynamicFilterCombine += "}";
  let data = filter_and_paginate(
    count,
    secondPart,
    elementId,
    apiToken, 
    websitePropertyId,
    forthPart,
    staticFilter,
    dynamicFilter,
    dynamicFilterCombine,
    staticFilterCombine
  );

  fetch(bb_plugin_data.ajaxurl, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(data).toString(),
  })
    .then((response) => response.json())
    .then((data) => {
      try {
        var fav_data = data.data.bragbook_favorite;
        let heartImage;
        if (data.data && data.data.case_set) {
          let caseSet = JSON.parse(data.data.case_set);
          if (forthPart == "") {
            var bb_case_count = (count - 1) * 10;
            let contentBox = document.querySelector(".bb-content-boxes");

            caseSet.data.forEach((caseItem) => {
              if (caseItem.photoSets && caseItem.photoSets.length > 0) {
                let photoSet = caseItem.photoSets[0];
                let imgSrc =
                  photoSet.highResPostProcessedImageLocation ||
                  photoSet.postProcessedImageLocation ||
                  photoSet.originalBeforeLocation;
                let imgAlt = photoSet.seoAltText || "Procedure Image";
                let caseId = caseItem.id;
                if(fav_data.includes(caseId)) {
                  heartImage = bb_plugin_data.heartBordered;
                }else {
                  heartImage = bb_plugin_data.heartRed;
                }
                let caseDetails = caseItem.details || "";
                caseItem.patientCount = ++bb_case_count;
                let procedureUrl = `/${secondPart}/${thirdPart}/${caseId}/`;

                let newContentF = `
                            <div class="bb-content-box">
                                <div class="bb-content-thumbnail">
                                    <a href="${procedureUrl}">
                                        <img src="${imgSrc}" alt="${imgAlt}">
                                    </a>
                                    <img class="bb-heart-icon bb-open-fav-modal" 
                                        data-case-id="${caseId}"
                                        data-bb_api_token="${apiToken}" 
                                        data-bb_website_id="${websitePropertyId}" 
                                        src="${heartImage}" 
                                        alt="heart">
                                </div>
                                <div class="bb-content-box-inner">
                                    <div class="bb-content-box-inner-left">
                                        <h5>${thirdPart} : Patient ${caseItem.patientCount}</h5>
                                        <p>${caseDetails}</p> 
                                    </div>
                                    <div class="bb-content-box-inner-right">
                                        <img class="bb-open-fav-modal" 
                                            data-case-id="${caseId}" 
                                            data-bb_api_token="${apiToken}" 
                                            data-bb_website_id="${websitePropertyId}" 
                                            src="${heartImage}" 
                                            alt="heart">
                                    </div>
                                </div>
                                <div class="bb-content-box-cta">
                                    <a class="view-more-btn" href="${procedureUrl}">
                                        View More
                                    </a>
                                </div>
                            </div>
                        `;

                contentBox.innerHTML += newContentF;
              }
            });
          }
        } else {
          console.error("Invalid response structure:", data);
        }
      } catch (err) {
        console.error("Error parsing case_set JSON:", err);
      }
    });
  document.querySelector(".apply_bb_filter").innerHTML = `Apply`;
}
function bb_advance_filter() {
  let filterAccInner = document.querySelectorAll(
    ".bb-filter-content-inner-wrapper .accordion"
  );

  for (let i = 0; i < filterAccInner.length; i++) {
    filterAccInner[i].addEventListener("click", function () {
      this.classList.toggle("active");
      var panel = this.nextElementSibling;
      if (panel.style.maxHeight) {
        panel.style.maxHeight = null;
      } else {
        panel.style.maxHeight = panel.scrollHeight + "px";
      }
    });
  }
  const checkboxes = document.querySelectorAll(
    '.bb-checkbox-container input[type="checkbox"]'
  );
  const clearButton = document.getElementById("clearButton");
  clearButton.addEventListener("click", function () {
    checkboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });
    const contentBoxes = document.querySelectorAll(".bb-content-box");
    contentBoxes.forEach((box) => {
      box.style.display = "block";
    });
  });
}
function filter_and_paginate(
  count,
  secondPart,
  elementId,
  apiToken,
  websitePropertyId,
  forthPart,
  staticFilter, 
  dynamicFilter,
  dynamicFilterCombine,
  staticFilterCombine
) {
  const data = { 
    action: "bb_case_api",
    count: count,
    pageSlug: secondPart,
    procedureId: elementId,
    apiToken: apiToken,
    websitePropertyId: websitePropertyId,
    caseId: forthPart,
    staticFilter: staticFilter,
    dynamicFilter: dynamicFilter,
    dynamicFilterCombine: Object.keys(dynamicFilterCombine).length?JSON.stringify(dynamicFilterCombine):0,
    ...staticFilterCombine
  };
  return data;
}
// Function to generate pagination data
function generatePagination(caseIds, currentCaseId) {
  return caseIds.map((id, index) => ({
    id,
    caseNumber: index + 1, // Page numbers start from 1
    isCurrent: id === currentCaseId,
  }));
}

function renderPagination(paginationData, caseId, targetHref) {
  const paginationList = document.getElementById(`pagination-list-${caseId}`);
  if (!paginationList || !paginationData.length) return;

  paginationList.innerHTML = "";
  let baseUrl = window.location.origin + targetHref;

  // Find the current page index
  const currentPageIndex = paginationData.findIndex(
    (item) => item.id === caseId
  );
  const totalPages = paginationData.length;

  const hasPrevious = currentPageIndex > 0;
  const hasNext = currentPageIndex < totalPages - 1;

  // Previous button
  if (hasPrevious) {
    const prevPageId = paginationData[currentPageIndex - 1].id;
    const prevItem = document.createElement("li");
    prevItem.innerHTML = `<a href="${baseUrl}${prevPageId}">Previous</a>`;
    paginationList.appendChild(prevItem);
  }

  // Define the pagination window (4 pages)
  let start = Math.max(0, currentPageIndex - 2);
  let end = Math.min(totalPages, start + 4);

  // Adjust window if near start or end
  if (end - start < 4) {
    start = Math.max(0, end - 4);
  }

  // Page numbers (show only 4)
  for (let i = start; i < end; i++) {
    const pageItem = paginationData[i];
    const pageUrl = `${baseUrl}${pageItem.id}`;
    const listItem = document.createElement("li");
    listItem.className =
      pageItem.id === caseId ? "bb-single-case active" : "bb-single-case";
    listItem.innerHTML = `<a href="${pageUrl}">${pageItem.caseNumber}</a>`;
    paginationList.appendChild(listItem);
  }

  // Next button
  if (hasNext) {
    const nextPageId = paginationData[currentPageIndex + 1].id;
    const nextItem = document.createElement("li");
    nextItem.innerHTML = `<a href="${baseUrl}${nextPageId}">Next</a>`;
    paginationList.appendChild(nextItem);
  }
}

let filterBtn = document.querySelector(".bb-filter-heading");
let filterContent = document.querySelector(".bb-filter-content");

document.addEventListener("DOMContentLoaded", () => {
  if (filterBtn) {
    filterBtn.addEventListener("click", () => {
      filterContent.classList.toggle("active");
    });
  }

  const clickables = document.getElementsByClassName("toggle-on-click");

  if (clickables.length >= 1) {
    const clickablesArr = Array.from(clickables);
    clickablesArr.forEach((item) => {
      const allClasses = item.classList;
      const toggleClasses = [];
      allClasses.forEach((classname) => {
        if (classname.startsWith("toggle-")) {
          toggleClasses.push(classname.slice(7));
        }
      });

      item.addEventListener("click", () => {
        toggleClasses.forEach((classname) => {
          const divToToggle = document.getElementsByClassName(classname)[0];
          if (divToToggle) {
            divToToggle.classList.toggle("isActive");
          }
        });
        item.classList.toggle("isActive");
      });
    });
  }

  const clickables2 = document.getElementsByClassName(
    "toggle-on-click-multiple"
  );
  const clickables2Close = document.getElementsByClassName(
    "toggle-on-click-multiple-close"
  );

  if (clickables2.length >= 1) {
    const clickablesArr = Array.from(clickables2);

    const clickablesArrClose = Array.from(clickables2Close);
    clickablesArrClose.forEach((item) => {
      const allClasses = item.classList;
      const toggleClasses = [];
      allClasses.forEach((classname) => {
        if (classname.startsWith("toggle-")) {
          toggleClasses.push(classname.slice(7));
        }
      });

      // Add click event listener to toggle the classes
      item.addEventListener("click", () => {
        toggleClasses.forEach((classname) => {
          const divToToggle = document.getElementsByClassName(classname)[0];

          if (divToToggle) {
            divToToggle.classList.remove("isActive");
          }
        });
        // Toggle "isActive" class on the clicked item
        clickablesArr.forEach((item) => item.classList.remove("isActive"));
        item.classList.toggle("isActive");
      });
    });
  }
});
let bb_acc = Array.from(document.querySelectorAll(".bb-accordion"));

function closeAllPanels() {
  for (let i = 0; i < bb_acc.length; i++) {
    bb_acc[i].classList.remove("active");
    let panel = bb_acc[i].nextElementSibling;
    panel.style.maxHeight = null;
  }
}

for (let i = 0; i < bb_acc.length; i++) {
  bb_acc[i].addEventListener("click", function () {
    if (!this.classList.contains("active")) {
      closeAllPanels();
    }
    this.classList.toggle("active");
    let panel = this.nextElementSibling;
    if (panel.style.maxHeight) {
      panel.style.maxHeight = null;
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
    }
  });
}

jQuery(document).ready(function ($) {
  // SLIDER
  let bb_slider;
  if ($.fn.slick) {
     bb_slider = $("body .bb-slider").slick({
      // 	   $('body .bb-slider').slick({
      infinite: true,
      slidesToShow: 3,
      slidesToScroll: 1,
      prevArrow: `<img class="bb-arrow bb-left-arrow" src="${bb_plugin_data.leftArrow}">`,
      nextArrow: `<img class="bb-arrow bb-right-arrow" src="${bb_plugin_data.rightArrow}">`,
      responsive: [
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: 2,
          },
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 1,
          },
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1,
            prevArrow: `<img class="bb-arrow bb-left-arrow" src="${bb_plugin_data.leftArrowUrl}">`,
            nextArrow: `<img class="bb-arrow bb-right-arrow" src="${bb_plugin_data.rightArrowUrl}">`,
          },
        },
      ],
    });
  } else {
    console.error("Slick Carousel is not loaded.");
  }
  const pageContainer = document.querySelector("body");

  const pageContainerWidth = pageContainer.offsetWidth;

  if (pageContainerWidth <= 1200 && pageContainerWidth >= 768) {
    bb_slider.slick("slickSetOption", "slidesToShow", 2, true);
  } else if (pageContainerWidth <= 768) {
    bb_slider.slick("slickSetOption", "slidesToShow", 1, true);
    bb_slider.slick(
      "slickSetOption",
      "prevArrow",
      `<img class="bb-arrow bb-left-arrow" src="${bb_plugin_data.leftArrowUrl}">`,
      true
    );
    bb_slider.slick(
      "slickSetOption",
      "nextArrow",
      `<img class="bb-arrow bb-right-arrow" src="${bb_plugin_data.rightArrowUrl}">`,
      true
    );
  }
  $(document).on("click", "#bb_update_api", function (e) {
    e.preventDefault();
    $(".update-api-status").text("");

    $.ajax({
      url: bb_plugin_data.ajaxurl,
      method: "POST",
      data: {
        action: "bb_update_api",
      },
      beforeSend: function () {
        $(".update-api").text("Loading...");
        $(this).prop("disabled", true);
      },
      success: function (response) {
        if (response.success) {
          $(".update-api").text("");
          $(".update-api-status").text("API Updated Successfully!");
          setTimeout(function () {
            $(".update-api-status").text("");
          }, 10000);
        } else {
          $(".update-api").text("");
          $(".update-api-status").text("API Update Failed!");
          setTimeout(function () {
            $(".update-api-status").text("");
          }, 10000);
        }
      },
      error: function () {
        alert("An error occurred while loading more items.");
        button.text("View More").prop("disabled", false);
        $(".update-api-status").text("Error occurred while updating API.");
      },
    });
  });

  $(document).on("click", ".ajax-load-more-btn", function (e) {
    e.preventDefault();

    var button = $(this);
    var offset = button.data("offset");
    var items_per_page = 10;

    // Make the AJAX request
    $.ajax({
      url: bb_plugin_data.ajaxurl,
      method: "POST",
      data: {
        action: "load_more_procedures",
        offset: offset,
        items_per_page: items_per_page,
      },
      beforeSend: function () {
        button.text("Loading...").prop("disabled", true);
      },
      success: function (response) {
        if (response.success) {
          $(".bb-content-boxes>.ajax-load-more").before(
            response.data.items_html
          );

          button.data("offset", offset + items_per_page);

          if (!response.data.has_more) {
            button.hide();
          } else {
            button.text("View More").prop("disabled", false);
          }
        }
      },
      error: function () {
        alert("An error occurred while loading more items.");
        button.text("View More").prop("disabled", false);
      },
    });
  });

  $(".age-validation-modal").hide();
  $(".over_181").click(function () {
    $(".age-validation-modal").hide();
  });

  // category active
  function getCatTitleFromUrl() {
    var catTitle = null;
    var path = window.location.pathname;
    var regex = /\/cat_title\/([^\/]+)\/?$/;
    var bb_cat_match = path.match(regex);

    if (bb_cat_match && bb_cat_match[1]) {
      catTitle = decodeURIComponent(bb_cat_match[1]);
    }
    if (!catTitle) {
      catTitle = "Face";
    }
    return catTitle;
  }

  function activateAccordionAndPanel(catTitle) {
    if (!catTitle) return;

    var accordions = document.querySelectorAll(".bb-accordion");

    $(".bb-panel ul li a").each(function () {
      var accordion_anchor = $(this).closest(".bb-panel").prev(".bb-accordion");
      var cat_attr = accordion_anchor.attr("cat_title");
      var procedrue_path = window.location.pathname;
      if (
        cat_attr === catTitle &&
        procedrue_path.split("/")[1] !== "procedure-case"
      ) {
        var accordionButton = $(this)
          .closest(".bb-panel")
          .prev(".bb-accordion");
        accordionButton.addClass("active");
        accordionButton.next(".bb-panel").slideDown();
        var panel = accordionButton.next(".bb-panel")[0]; // Get the DOM element
        if (panel) {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      }
    });
  }

  var _catTitle = getCatTitleFromUrl();
  if (_catTitle) {
    // activateAccordionAndPanel(_catTitle);
  }

  // Function to check and highlight matching procedure_id
  function highlightMatchingIds() {
    // Loop through each anchor tag
    var bb_c = 0;
    $(".bb-panel ul li a").each(function () {
      // Extract text excluding <span> content
      var bragbook_procedure_text = $(this)
        .contents()
        .filter(function () {
          return this.nodeType === Node.TEXT_NODE;
        })
        .text()
        .trim();

      // Remove trailing hyphens
      bragbook_procedure_text = bragbook_procedure_text.replace(/-/g, " ");
      bragbook_procedure_text = bragbook_procedure_text.toLowerCase();
      var currentUrl = window.location.href;
      var href = currentUrl;
      var procedureNameFromUrl = getProcedureNameFromUrl(href);
      procedureNameFromUrl = procedureNameFromUrl.toLowerCase();
      if (procedureNameFromUrl === null && bragbook_procedure_text !== null) {
        bb_c++;
      }

      if (bragbook_procedure_text === procedureNameFromUrl && bb_c <= 1) {
        // Add style to highlight the id
        $(this).css("color", "#000");
        $(this).css("opacity", "1");
        // Open the corresponding accordion panel
        var accordionButton = $(this)
          .closest(".bb-panel")
          .prev(".bb-accordion");
        accordionButton.addClass("active");
        accordionButton.next(".bb-panel").slideDown();
        var panel = accordionButton.next(".bb-panel")[0]; // Get the DOM element
        if (panel) {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      }
    });
  }

  // // Helper function to extract procedure_id from URL
  function getProcedureNameFromUrl(url) {
    // Parse the URL to get the pathname
    var pathname = new URL(url).pathname;
    var pathParts = pathname.split("/").filter(Boolean);

    // Check if the second-to-last segment is the one we're interested in
    var procedureSegment =
      pathParts.length > 2
        ? pathParts[pathParts.length - 2]
        : pathParts[pathParts.length - 1];

    // Replace dashes with spaces
    var cleanedText = procedureSegment
      ? procedureSegment.replace(/-/g, " ")
      : "";

    return cleanedText;
  }
  // Call the function to highlight matching ids when the page loads
  //highlightMatchingIds();

  var $pagination = $(".pagination");
  var currentPage = $pagination.data("current-page");
  var totalPages = $pagination.data("total-pages");

  // Function to update pagination buttons
  function updatePaginationButtons(currentPage, totalPages) {
    // Hide/show Previous button based on current page
    $(".load-more-btn.prev").toggle(currentPage > 1);

    // Hide/show Next button based on current page
    $(".load-more-btn.next").toggle(currentPage < totalPages);

    // Hide/show page numbers based on total pages
    $(".page-number").toggle(totalPages > 1);

    // Show current page and its neighbors
    $(".page-number").each(function () {
      var page = parseInt($(this).data("page"));
      $(this).toggle(
        page === currentPage ||
          page === currentPage - 1 ||
          page === currentPage + 1
      );
    });
  }

  // Initial setup
  updatePaginationButtons(currentPage, totalPages);

  // Click event for Previous and Next buttons
  $(document).on("click", ".load-more-btn", function (e) {
    e.preventDefault();

    var $this = $(this);
    var currentPage = $this.data("page");
    var nextPage = $this.hasClass("next") ? currentPage + 1 : currentPage - 1;

    $.ajax({
      url: bb_plugin_data.ajaxurl,
      type: "post",
      dataType: "html",
      data: {
        action: "load_form_entries",
        page: nextPage,
      },
      beforeSend: function () {
        $this.prop("disabled", true).text("Loading...");
      },
      success: function (response) {
        $(".form-entries-table tbody").html(response);

        // Highlight the current page number
        $(".page-number.active").removeClass("active");
        $('.page-number[data-page="' + nextPage + '"]').addClass("active");

        // Update data-page attribute of buttons
        $(".load-more-btn").data("page", nextPage);

        // Update pagination buttons visibility
        updatePaginationButtons(nextPage, totalPages);
      },
      complete: function () {
        $this
          .prop("disabled", false)
          .text($this.hasClass("next") ? "Next" : "Previous");
      },
      error: function (xhr, status, error) {
        console.error(error);
        $this
          .prop("disabled", false)
          .text($this.hasClass("next") ? "Next" : "Previous");
      },
    });
  });

  // Click event for page numbers
  $(document).on("click", ".page-number", function (e) {
    e.preventDefault();

    var $this = $(this);
    var currentPage = $this.data("page");

    $.ajax({
      url: bb_plugin_data.ajaxurl,
      type: "post",
      dataType: "html",
      data: {
        action: "load_form_entries",
        page: currentPage,
      },
      beforeSend: function () {
        $(".load-more-btn").prop("disabled", true).text("Loading...");
      },
      success: function (response) {
        $(".form-entries-table tbody").html(response);

        // Highlight the current page number
        $(".page-number.active").removeClass("active");
        $this.addClass("active");

        // Update data-page attribute of buttons
        $(".load-more-btn").data("page", currentPage);

        // Update pagination buttons visibility
        updatePaginationButtons(currentPage, totalPages);
      },
      complete: function () {
        $(".load-more-btn").prop("disabled", false);
      },
      error: function (xhr, status, error) {
        console.error(error);
        $(".load-more-btn").prop("disabled", false);
      },
    });
  });

  // ajax request for button

  $(".bb-form").submit(function (event) {
    // Prevent default form submission
    event.preventDefault();

    // Get form data
    var formData = $(this).serialize();

    // AJAX request
    $.ajax({
      type: "POST",
      url: bb_plugin_data.ajaxurl, // WordPress AJAX URL
      data: formData + "&action=handle_form_submission", // Add action parameter
      beforeSend: function () {
        $(".bb-is-required-success").text("Submitting form...");
      },
      success: function (response) {
        var successMessage = response.data;
        $(".bb-is-required-success").text(successMessage);
        // $(".bb-consultation-form").addClass("bb-display-none");
      },
      error: function (xhr, status, error) {
        // Handle error
        $(".bb-is-required-success").text(error);
      },
    });
  });

  // Check if any form exists in the bb-main area
  if ($(".bb-main .bb-form").length) {
    var $bb_form = $(".bb-main .bb-form");
    var $bb_inputs = $(".bb-main .bb-is-required");

    $bb_form.on("submit", function (e) {
      var bb_is_required = true;

      $bb_inputs.each(function () {
        if ($(this).val() === "") {
          bb_is_required = false;
          $(this).next().show();
        } else {
          $(this).next().hide();
        }
      });

      if (!bb_is_required) {
        e.preventDefault();
      } else {
        $(".bb-is-required-success").show();
      }
    });
  }

  // Toggle functionality for both sidebar toggle buttons
  $(".bb-sidebar-toggle").on("click", function () {
    // Toggle the sidebar visibility
    $(".bb-sidebar").toggleClass("active"); // Example to add a class
    $(this).toggleClass("active"); // Toggle the active class for the button
  });

  // Intercept the form submission
  $("#bragbook_setting_page").on("submit", function (e) {
    e.preventDefault(); // Prevent the default form submission
    $(".bb-save-api-settings-status").text("");
    const dataToSend = [];
    const inputs = document.querySelectorAll(
      'input[name^="bb_gallery_page_slug"]'
    );
    inputs.forEach((input) => {
      dataToSend.push({
        key: input.dataset.key,
        value: input.value,
      });
    });
    // Collect form data
    var formData = $(this).serialize();

    // Send AJAX request
    $.ajax({
      url: bb_plugin_data.ajaxurl, // WordPress AJAX URL
      type: "POST",
      data: {
        action: "bb_save_bragbook_settings", // Action to trigger on server-side
        form_data: formData, // Pass serialized form data
        bb_page_keys: dataToSend,
      },
      beforeSend: function () {
        $(".bb-save-api-status").text("Loading...");
        $(this).prop("disabled", true);
      },
      success: function (response) {
        if (response.success) {
          $(".bb-save-api-status").text("");
          $(".bb-save-api-settings-status").text(
            "Settings saved successfully."
          );
          setTimeout(function () {
            $(".bb-save-api-settings-status").text("");
          }, 10000);
        } else {
          $(".bb-save-api-status").text("");
          $(".bb-save-api-settings-status").text(
            "There was an error saving the settings."
          );
          setTimeout(function () {
            $(".bb-save-api-settings-status").text("");
          }, 10000);
        }
      },
      error: function () {
        alert("AJAX request failed.");
      },
    });
  });
  // Prevent page reload when clicking the submit button directly
  $("#bragbook_seeting_form")
    .find('input[type="submit"]')
    .on("click", function (e) {
      e.preventDefault();
      $("#bragbook_seeting_form").trigger("submit");
    });
});
function closeModal() {
  var modal = document.querySelector(".bb-fav-modal");
  if (modal) {
    modal.classList.remove("is-open"); 
      var opacity = 1;
      opacity -= 0.05;
      if (opacity <= 0) {
        modal.style.opacity = 0;
        modal.classList.remove("is-open");
        return true;
      }
      modal.style.opacity = opacity;
      
  }
}
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.querySelector(".bb-fav-modal");
  const modalInner = document.querySelector(".bb-fav-modal-inner");

  // Check if modalInner exists
  let form, caseIdInput, bbApiTokenInput, bbWebsiteIdInput;

  if (modalInner) {
    form = modalInner.querySelector("form");
    caseIdInput = modalInner.querySelector("input[name='case-id']");
    bbApiTokenInput = modalInner.querySelector("input[name='api-token']");
    bbWebsiteIdInput = modalInner.querySelector("input[name='website-id']");
  }

  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
  }

  // Function to open the modal
  function openModal(caseId, bbApiToken, bbWebsiteId) {
    // Set the caseId value in the form if the inputs exist
    if (caseIdInput) caseIdInput.value = caseId;
    if (bbApiTokenInput) bbApiTokenInput.value = bbApiToken;
    if (bbWebsiteIdInput) bbWebsiteIdInput.value = bbWebsiteId;

    var encodedCookieValue = getCookie("wordpress_favorite_email");
    if (encodedCookieValue !== undefined) {
      var bb_favorite_email = decodeURIComponent(encodedCookieValue);
      var bb_favorite_name = decodeURIComponent(getCookie("wordpress_favorite_name"));
      var bb_favorite_phone = decodeURIComponent(getCookie("wordpress_favorite_phone"));
      var bb_favorite_case_id = decodeURIComponent(getCookie("wordpress_favorite_case_id"));
      var bb_favorite_api_token = decodeURIComponent(getCookie("wordpress_favorite_api_token"));
      var bb_favorite_website_id = decodeURIComponent(getCookie("wordpress_favorite_website_id"));

      var bb_fav_list_cookie = bb_favorite_case_id.split(",").map(Number);
      var bb_exist_list = new Set(bb_fav_list_cookie);
      var bb_exist = bb_exist_list.has(Number(caseId));

      if (bb_exist) {
        alert("Already favorite!");
        return false;
      } else {
        var data_cookie = {
          email: bb_favorite_email,
          phone: bb_favorite_phone,
          name: bb_favorite_name,
          caseIds: [caseId],
          bbApiTokens: [bbApiToken],
          bbWebsiteIds: [bbWebsiteId],
        };
        bb_favorites_submission(data_cookie);
      }
    } else {
      fadeIn(modal);
    }
  }

  // Function to close the modal
  function closeModal() {
    fadeOut(modal);
  }

  // Add event listener to the close button (X)
  const closeButton = modalInner?.querySelector(".bb-fav-modal-close-button");
  if (closeButton) {
    closeButton.addEventListener("click", function (e) {
      e.stopPropagation(); // Prevent event from bubbling
      closeModal();
    });
  }

  // Event listener for modal toggles (open modal)
  document.body.addEventListener("click", function (e) {
    const target = e.target;

    if (target && target.classList.contains("bb-open-fav-modal")) {
      e.stopPropagation();
      const caseId = target.getAttribute("data-case-id");
      const bbApiToken = target.getAttribute("data-bb_api_token");
      const bbWebsiteId = target.getAttribute("data-bb_website_id");
      openModal(caseId, bbApiToken, bbWebsiteId);
    }
  });

  // Close the modal when clicking outside the modal content
  document.addEventListener("click", function (event) {
    if (modal && modal.classList.contains("is-open") && !modalInner.contains(event.target)) {
      closeModal();
    }
  });

  // Form submission handler
  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      // Ensure the inputs are defined before using
      const caseId = caseIdInput ? caseIdInput.value : '';
      const bbApiToken = bbApiTokenInput ? bbApiTokenInput.value : '';
      const bbWebsiteId = bbWebsiteIdInput ? bbWebsiteIdInput.value : '';

      var formData = new FormData(form);
      var data = {
        email: formData.get("email"),
        phone: formData.get("number"),
        name: formData.get("name"),
        caseIds: [caseId],
        bbApiTokens: [bbApiToken],
        bbWebsiteIds: [bbWebsiteId],
      };
      bb_favorites_submission(data);
    });
  }

  // Submission logic
  function bb_favorites_submission(data) {
    jQuery.ajax({
      url: bb_plugin_data.ajaxurl,
      type: "POST",
      data: {
        action: "bragbook_my_favorite",
        email: data.email,
        phone: data.phone,
        name: data.name,
        caseIds: data.caseIds,
        bbApiTokens: data.bbApiTokens,
        bbWebsiteIds: data.bbWebsiteIds,
      },
      success: function (response) {
        if (response.success) {
          var imgElement = jQuery(`img[data-case-id="${data.caseIds[0]}"]`);
          if (imgElement.length) {
            imgElement.each(function () {
              jQuery(this).attr("src", bb_plugin_data.heartBordered);
            });
          }
          var $span = jQuery("a.bb-sidebar_favorites span");
          var text = $span.text();
          var match = text.match(/\((\d+)\)/);
          if (match) {
            var currentValue = parseInt(match[1], 10);
            var newValue = currentValue + 1;
            $span.text(`(${newValue})`);
          }
          closeModal();
        } else {
          alert("Failed to save favorite.");
          closeModal();
        }
      },
      error: function (error) {
        console.error("Error:", error);
        closeModal();
      },
    });
  }

  // Fade functions
  function fadeOut(element) {
    var opacity = 1;
    function decrease() {
      opacity -= 0.05;
      if (opacity <= 0) {
        element.style.opacity = 0;
        element.classList.remove("is-open");
        return true;
      }
      element.style.opacity = opacity;
      requestAnimationFrame(decrease);
    }
    decrease();
  }

  function fadeIn(element) {
    var opacity = 0;
    element.classList.add("is-open");
    function increase() {
      opacity += 0.05;
      if (opacity >= 1) {
        element.style.opacity = 1;
        return true;
      }
      element.style.opacity = opacity;
      requestAnimationFrame(increase);
    }
    increase();
  }
});



Array.from(document.querySelectorAll(".bb-filter-select")).forEach((filter) => {
  if (filter) {
    filter
      .querySelector(".bb-filter-heading")
      ?.addEventListener("click", () => filter?.classList.toggle("active"));
  }
});
Array.from(document.querySelectorAll(".bb-filter-toggle")).forEach((filter) => {
  if (filter) {
    filter.addEventListener("click", () =>
      document.querySelector(".bb-filter-select").classList.toggle("active")
    );
  }
});

//consultation form validation
if (document.querySelector(".bb-main .bb-form")) {
  let bb_form = document.querySelector(".bb-main .bb-form");
  let bb_inputs = Array.from(
    document.querySelectorAll(".bb-main .bb-is-required")
  );
  bb_form.addEventListener("submit", (e) => {
    let bb_is_required = true;
    bb_inputs.forEach((input) => {
      if (input.value === "") {
        bb_is_required = false;
        input.nextElementSibling.style.display = "block";
      } else {
        input.nextElementSibling.style.display = "none";
      }
    });
    if (!bb_is_required) {
      e.preventDefault();
    } else {
      document.querySelector(".bb-is-required-success").style.display = "block";
    }
  });
}
setTimeout(function () {
  modalInitBB();
}, 8000);
function modalInitBB() {
  const bbrag_modal = document.getElementById("bbrag_modal");
  const bbrag_modalImage = document.getElementById("bbrag_modalImage");
  const bbrag_closeModal = document.querySelector(".bbrag_close");
  const bbrag_prevArrow = document.querySelector(".bbrag_prev");
  const bbrag_nextArrow = document.querySelector(".bbrag_next");
  const bbrag_images = document.querySelectorAll(".bbrag_gallery_image");
  let bbrag_currentIndex = 0;

  bbrag_images.forEach((img, index) => {
    img.addEventListener("click", () => {
      bbrag_currentIndex = index;
      bbrag_modal.style.display = "block";
      bbrag_modalImage.src = bbrag_images[bbrag_currentIndex].src;
    });
  });

  if (bbrag_closeModal) {
    bbrag_closeModal.addEventListener("click", () => {
      bbrag_modal.style.display = "none";
    });
  }

  if (bbrag_prevArrow) {
    bbrag_prevArrow.addEventListener("click", () => {
      bbrag_currentIndex =
        (bbrag_currentIndex - 1 + bbrag_images.length) % bbrag_images.length;
      bbrag_modalImage.src = bbrag_images[bbrag_currentIndex].src;
    });
  }

  if (bbrag_nextArrow) {
    bbrag_nextArrow.addEventListener("click", () => {
      bbrag_currentIndex = (bbrag_currentIndex + 1) % bbrag_images.length;
      bbrag_modalImage.src = bbrag_images[bbrag_currentIndex].src;
    });
  }

  window.addEventListener("click", (event) => {
    if (event.target === bbrag_modal) {
      bbrag_closeModalHandler();
    }
  });
}
