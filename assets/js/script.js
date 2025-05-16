let loadMoreCount = 1;
const filterBtn = document.querySelector(".bb-filter-heading");
const filterContent = document.querySelector(".bb-filter-content");
const accordion = Array.from(document.querySelectorAll(".bb-accordion"));

const pathSegments = window.location.pathname.split("/").filter(Boolean);
const isCarouselPage = pathSegments.length === 1;
const isListsPage = pathSegments.length === 2;
const isViewMoreDetailPage = pathSegments.length === 3;
const isFavoriteListPage = pathSegments[1] === "favorites" && pathSegments.length === 2;
const isConsultationPage = pathSegments[1] === "consultation" && pathSegments.length === 2;
const isValidPathLength = pathSegments.length > 1 && pathSegments.length < 4;
const isNotSpecialPage = !isFavoriteListPage && !isConsultationPage;

let linkText;

document.addEventListener("DOMContentLoaded", () => {
  fetchCaseData(loadMoreCount);
  handleLoadMoreButton();
  handleFilterToggle();
  handleSingleClickToggles();
  handleMultipleClickToggles();
  handleConsultationForm();
});

function handleLoadMoreButton(show) {
  const loadMoreContainer = document.querySelector(".ajax-load-more");
  if (loadMoreContainer) {
    const loadMoreButton = loadMoreContainer.querySelector(".bb_ajax-load-more-btn");
    if (!loadMoreContainer || !loadMoreButton) {
      console.error("Load more container not found");
      return;
    }
    const currentOffset = parseInt(loadMoreButton.getAttribute("data-offset"), 10);
    if (!show || currentOffset === 1) loadMoreContainer.style.display = "none";
    if (show) loadMoreContainer.style.display = "flex";
  }
}

function handleFilterToggle() {
  if (filterBtn) filterBtn.addEventListener("click", () => filterContent.classList.toggle("active"));
}

function handleSingleClickToggles() {
  const clickables = document.getElementsByClassName("toggle-on-click");
  if (clickables.length === 0) return;

  Array.from(clickables).forEach((item) => {
    const toggleClasses = getToggleClasses(item);
    item.addEventListener("click", () => {
      toggleClasses.forEach((classname) => toggleElement(classname));
      item.classList.toggle("isActive");
    });
  });
}

function handleMultipleClickToggles() {
  const clickables = document.getElementsByClassName("toggle-on-click-multiple");
  const clickablesClose = document.getElementsByClassName("toggle-on-click-multiple-close");

  if (clickablesClose.length === 0) return;

  const clickablesArr = Array.from(clickables);
  Array.from(clickablesClose).forEach((item) => {
    const toggleClasses = getToggleClasses(item);

    item.addEventListener("click", () => {
      toggleClasses.forEach((classname) => removeActiveClass(classname));
      clickablesArr.forEach((clickable) => clickable.classList.remove("isActive"));
      item.classList.toggle("isActive");
    });
  });
}

function getToggleClasses(element) {
  return Array.from(element.classList)
    .filter((classname) => classname.startsWith("toggle-"))
    .map((classname) => classname.slice(7));
}

function toggleElement(classname) {
  const element = document.querySelector(`.${classname}`);
  if (element) element.classList.toggle("isActive");
}

function removeActiveClass(classname) {
  const element = document.querySelector(`.${classname}`);
  if (element) element.classList.remove("isActive");
}

// ============================ Event Listener ===========================================
document.addEventListener("click", (event) => {
  const loadMoreContainer = event.target.closest(".ajax-load-more");
  if (!loadMoreContainer) return;

  event.preventDefault();
  const loadMoreButton = loadMoreContainer.querySelector(".bb_ajax-load-more-btn");
  if (loadMoreButton) {
    let currentOffset = parseInt(loadMoreButton.getAttribute("data-offset"), 10);
    fetchCaseData(currentOffset);
    loadMoreButton.setAttribute("data-offset", ++currentOffset);
  }
});

// ============================ Fetch Data ===========================================
function fetchCaseData(loadMoreCount) {
  try {
    let count = loadMoreCount;
    const pageSlug = pathSegments[0] || "";
    const procedureSlug = pathSegments[1] || "";
    const caseIdentifier = pathSegments[2]?.includes("bb-case") ? pathSegments[2].split("-").pop() : "";
    let seoSuffixUrl = caseIdentifier ? "" : pathSegments[2] || "";

    const targetLinkSelector = `/${pageSlug}/${procedureSlug}/`;
    const targetLinkElement = document.querySelector(`a[href="${targetLinkSelector}"]`);

    const elementId = targetLinkElement?.id || "";
    const apiToken = targetLinkElement?.getAttribute("data-api-token");
    const websitePropertyId = targetLinkElement?.getAttribute("data-website-property-id");
    if (targetLinkElement) {
      linkText = Array.from(targetLinkElement.childNodes)
        .filter((node) => node.nodeType === Node.TEXT_NODE)
        .map((node) => node.textContent.trim())
        .join(" ");

      Object.assign(targetLinkElement.style, { color: "#000", opacity: "1" });

      const accordionButton = targetLinkElement.closest(".bb-panel")?.previousElementSibling;
      if (accordionButton?.classList.contains("bb-accordion")) {
        accordionButton.classList.add("active");

        const accordionPanel = accordionButton.nextElementSibling;
        if (accordionPanel?.classList.contains("bb-panel")) {
          accordionPanel.style.display = "block";
          accordionPanel.style.maxHeight = `${accordionPanel.scrollHeight}px`;
        }
      }
    }

    const getSelectedCheckboxes = (selector) => {
      return Array.from(document.querySelectorAll(selector)).map((checkbox) => ({
        key: checkbox.getAttribute("data-key"),
        value: checkbox.getAttribute("data-value"),
      }));
    };

    const processFilterSelection = (selectedCheckboxes, isDynamic = false) => {
      let queryString = "";
      let filterData = {};

      selectedCheckboxes.forEach(({ key, value }) => {
        const formattedKey = isDynamic ? key.replace(/\s+/g, "|||") : key;
        const formattedValue = isNaN(parseInt(value)) ? value : parseInt(value);

        queryString += `&${formattedKey}=${value}`;
        filterData[formattedKey] = formattedValue;
      });

      return { queryString, filterData };
    };

    const staticFilters = getSelectedCheckboxes('.bb-static-filter input[type="checkbox"]:checked');
    const dynamicFilters = getSelectedCheckboxes('.bb-dynamic-filter input[type="checkbox"]:checked');

    const { queryString: staticFilterQuery, filterData: staticFilterData } = processFilterSelection(staticFilters);
    const { queryString: dynamicFilterQuery, filterData: dynamicFilterData } = processFilterSelection(dynamicFilters, true);

    const requestData = {
      action: "bb_case_api",
      count: loadMoreCount,
      pageSlug,
      favorites: isFavoriteListPage ? 'favorites' : null,
      procedureId: elementId,
      apiToken,
      websitePropertyId,
      caseId: caseIdentifier,
      seoSuffixUrl,
      staticFilter: staticFilterQuery,
      dynamicFilter: dynamicFilterQuery,
      dynamicFilterCombine: Object.keys(dynamicFilterData).length ? JSON.stringify(dynamicFilterData) : 0,
      ...staticFilterData,
    };

    fetch(bb_plugin_data.ajaxurl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded", },
      body: new URLSearchParams(requestData).toString(),
    }).then((response) => response.json())
      .then((data) => {

        const caseSets = JSON.parse(data.data.case_set);
        const filterSet = JSON.parse(data.data.filter_data);
        const currentPageSlug = data.data.page_slug_bb ?? data.data.combine_page_slug;
        const isSamePage = currentPageSlug === data.data.page_slug;
        const isPageValid = filterSet.success && caseSets.success;
        if (isSamePage && isValidPathLength && isNotSpecialPage && !isPageValid) window.location.href = '/page-not-found';

        const seopagetitle = data.data.seo_page_title;
        const myFavoriteCountSpan = document.getElementById("bb_favorite_caseIds_count");
        myFavoriteCountSpan ? myFavoriteCountSpan.style.display = 'inline' : '';
        try {
          let heartImage;
          let sidebarApi;
          let fav_data;
          let currentProcedure;
          if (data.data.sidebar_api) {
            try {
              const parsedSidebarApi = JSON.parse(JSON.parse(data.data.sidebar_api));
              sidebarApi = parsedSidebarApi.data?.flatMap(item => item.procedures) || [];
              currentProcedure = sidebarApi.find(pro => pro.slugName === procedureSlug);
              if (currentProcedure && currentProcedure.nudity && isListsPage) document.getElementById('popup').style.display = 'flex';
              displayProcedureTitle(sidebarApi, procedureSlug);
            } catch (error) {
              console.error("Error parsing sidebar_api:", error);
              sidebarApi = [];
            }
          }

          if (data.data.bragbook_favorite.length > 0) {
            fav_data = data.data.bragbook_favorite;
            let element_fav_count = document.getElementById("bb_favorite_caseIds_count");
            element_fav_count.textContent = "(" + `${fav_data.length}` + ")";
          }
          if (data.data && data.data.case_set) {
            let caseSet = JSON.parse(data.data.case_set);
            if (caseIdentifier == "" && !seoSuffixUrl) {
              if (isListsPage && !isFavoriteListPage) handleLoadMoreButton(caseSet.hasLoadMore);
              var bb_case_count = (count - 1) * 10;
              let contentBox = document.querySelector(".bb-content-boxes");
              const applyBBButton = document.querySelector(".apply_bb_filter");
              if (applyBBButton) applyBBButton.innerHTML = `Apply`;
              let images = [];
              if (caseSet.data) {
                let bb_gallery_page_title = pageSlug.split('-').map(w => w[0].toUpperCase() + w.slice(1)).join(' ');
                let bb_procedure_Title = procedureSlug.split('-').map(w => w[0].toUpperCase() + w.slice(1)).join(' ');
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
                    let caseId = null;
                    seoSuffixUrl = caseItem.caseDetails[0].seoSuffixUrl;
                    if (seoSuffixUrl) {
                      caseId = seoSuffixUrl;
                    } else {
                      caseId = "bb-case-" + caseItemId;
                    }
                    let procedureUrl = `/${pageSlug}/${procedureSlug}/${caseId}/`;

                    if (fav_data && fav_data.includes(caseItemId)) {
                      heartImage = bb_plugin_data.heartBordered;
                    } else {
                      heartImage = bb_plugin_data.heartRed;
                    }
                    let imageObj = {
                      "@type": "ImageObject",
                      "name": bb_procedure_Title,
                      "description": `Photo gallery of ${bb_procedure_Title} results showing before and after photos from different angles.`,
                      "url": `${targetLinkSelector}${caseId}`,
                      "thumbnailUrl": imgSrc
                    };

                    let proceduralName = "";
                    if (caseItem.caseDetails[0].seoHeadline) {
                      proceduralName = caseItem.caseDetails[0].seoHeadline;
                    } else {
                      let titleWithoutDashes = procedureSlug.replace(/-/g, ' ');
                      proceduralName = titleWithoutDashes + ': Patient ' + caseItem.patientCount;
                    }
                    images.push(imageObj);
                    let newContent = `
                              <div class="bb-content-box">
                                  <div class="bb-content-thumbnail">
                                      <a href="${procedureUrl}">
                                          <img src="${imgSrc}" alt="${imgAlt}">
                                      </a>
                                      <img class="bb-heart-icon bb-open-fav-modal 1" 
                                          data-case-id="${caseItemId}"
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
                                              data-case-id="${caseItemId}" 
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
                  "name": `${bb_procedure_Title} Before & After Gallery`,
                  "description": `Review ${caseSet.data.length} ${bb_procedure_Title} before and after cases. Each case includes photos from multiple angles, along with details about the procedure.`,
                  "url": `/${pageSlug}/${procedureSlug}/`,
                  "image": images,
                  "breadcrumb": {
                    "@type": "BreadcrumbList",
                    "itemListElement": [
                      {
                        "@type": "ListItem",
                        "position": 1,
                        "name": "Home",
                        "item": window.location.origin
                      },
                      {
                        "@type": "ListItem",
                        "position": 2,
                        "name": `${bb_gallery_page_title}`,
                        "item": `/${pageSlug}`
                      },
                      {
                        "@type": "ListItem",
                        "position": 3,
                        "name": `${bb_procedure_Title} Procedure`,
                        "item": `/${pageSlug}/${procedureSlug}`
                      }
                    ]
                  }
                };
                let schemaJson = JSON.stringify(schema, null, 4);
                let schemaScript = document.createElement('script');
                schemaScript.type = 'application/ld+json';
                schemaScript.innerHTML = schemaJson;
                document.head.appendChild(schemaScript);

              } else if (caseSet.favorites) {
                caseSet.favorites.forEach((caseItem) => {
                  if (caseItem.cases[0].photoSets && caseItem.cases[0].photoSets.length > 0 && sidebarApi) {
                    if (caseItem && caseItem.cases[0].procedureIds.length > 0) {
                      const isCombine = sidebarApi[0]?.ids;
                      let slugName;
                      if (isCombine) {
                        for (let i = 0; i < sidebarApi[0].ids.length; i++) {
                          if (slugName) break;
                          slugName = sidebarApi.find(p => p.ids[i] == caseItem.cases[0].procedureIds[0])?.slugName;
                        }
                      } else {
                        slugName = sidebarApi.find(p => p.id == caseItem.cases[0].procedureIds[0])?.slugName;
                      }

                      let photoSet = caseItem.cases[0].photoSets[0];
                      let imgSrc =
                        photoSet.highResPostProcessedImageLocation ||
                        photoSet.postProcessedImageLocation ||
                        photoSet.originalBeforeLocation;
                      let imgAlt = photoSet.seoAltText || "Procedure Image";
                      let caseItemId = caseItem.cases[0].id;
                      let caseDetails = caseItem.cases[0].details || "";
                      caseItem.patientCount = ++bb_case_count;

                      let caseId = "";
                      let seoSuffixUrl = caseItem.cases[0].caseDetails[0]?.seoSuffixUrl;
                      if (seoSuffixUrl) {
                        caseId = seoSuffixUrl;
                      } else {
                        caseId = "bb-case-" + caseItemId;
                      }

                      let procedureUrl = `/${pageSlug}/${slugName}/${caseId}/`;

                      heartImage = bb_plugin_data.heartBordered;

                      let newContent = `
                                <div class="bb-content-box">
                                    <div class="bb-content-thumbnail">
                                        <a href="${procedureUrl}">
                                            <img src="${imgSrc}" alt="${imgAlt}">
                                        </a>
                                        <img class="bb-heart-icon bb-open-fav-modal" 
                                            data-case-id="${caseItemId}"
                                            data-bb_api_token="${apiToken}" 
                                            data-bb_website_id="${websitePropertyId}" 
                                            src="${heartImage}" 
                                            alt="heart">
                                    </div>
                                    <div class="bb-content-box-inner">
                                        <div class="bb-content-box-inner-left">
                                            <h5>${procedureSlug} : Patient ${caseItem.patientCount}</h5>
                                            <p>${caseDetails}</p> 
                                        </div>
                                        <div class="bb-content-box-inner-right">
                                            <img class="bb-open-fav-modal" 
                                                data-case-id="${caseItemId}" 
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
              let proceduralName = "";
              if (patienLeftBox) {
                let titleWithoutDashes;
                let bbPatientNo = null;
                caseSet.data.forEach((caseItem) => {
                  titleWithoutDashes = procedureSlug.split('-').map(w => w[0].toUpperCase() + w.slice(1)).join(' ');
                  if (seoSuffixUrl) bbPatientNo = (caseItem.caseIds?.findIndex(item => item.seoSuffixUrl == seoSuffixUrl) + 1);
                  else if (caseIdentifier) bbPatientNo = (caseItem.caseIds?.findIndex(item => item.id == caseIdentifier) + 1);
                  let caseId = caseItem.id;
                  if (caseItem.caseDetails[0].seoHeadline) {
                    proceduralName = caseItem.caseDetails[0].seoHeadline;
                  } else {
                    proceduralName = titleWithoutDashes + ': Patient ' + bbPatientNo;
                  }
                  if (caseId == caseIdentifier || seoSuffixUrl == caseItem.caseDetails[0]?.seoSuffixUrl) {
                    if (caseItem.photoSets && caseItem.photoSets.length > 0) {
                      caseItem.photoSets.forEach((value, itemIndex) => {
                        let bb_new_image_value =
                          value.highResPostProcessedImageLocation ??
                          value.postProcessedImageLocation ??
                          value.originalBeforeLocation;
                        let imgElement = document.createElement("img");
                        imgElement.className =
                          "bbrag_gallery_image";
                        imgElement.src = bb_new_image_value;
                        imgElement.alt = (value.seoAltText ?? "Before and after " + proceduralName) + " - angle " + (itemIndex + 1);
                        patienLeftBox.appendChild(imgElement);
                        let imageObjc = {
                          "@type": "ImageObject",
                          "name": titleWithoutDashes,
                          "description": `Photo gallery of ${titleWithoutDashes} results showing before and after photos from different angles.`,
                          "url": `${targetLinkSelector}${caseItem.id}`,
                          "thumbnailUrl": bb_new_image_value
                        };

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
                  if (fav_data?.includes(caseItem.id)) {
                    heartImage = bb_plugin_data.heartBordered;
                  } else {
                    heartImage = bb_plugin_data.heartRed;
                  }
                  linkText += "Patient ";
                  if (seoSuffixUrl) linkText += caseItem.caseIds?.findIndex(item => item.seoSuffixUrl == seoSuffixUrl) + 1;
                  else if (caseIdentifier) linkText += caseItem.caseIds?.findIndex(item => item.id == caseIdentifier) + 1;
                  let bb_right_data = `
                        <div class="bb-patient-row">
                            <h2>${caseItem.caseDetails[0]?.seoHeadline || linkText}</h2>
                               <img class="bb-heart-icon bb-open-fav-modal" 
                                data-case-id="${caseItem.id}" 
                                data-bb_api_token="${apiToken}" 
                                data-bb_website_id="${websitePropertyId}" 
                                src="${heartImage}" alt="heart">
                        </div>
                        <ul class="bb-demographics">
                            ${height}
                            ${width}
                            ${race}
                            ${gender}
                            ${age}
                            ${timeframe}
                            ${timeframe2}
                            ${revisionSurgery}
                        </ul>
                        <div class="bb-case-description">${patientDetail}</div>
                         <div class="bb-patient-slides">
                          <ul id="pagination-list-${caseItem.id}" class="bb-pagination"></ul>
                        </div>
                    `;
                  patientRightBox.innerHTML += bb_right_data;

                  let paginationData = generatePagination(
                    caseItem.caseIds,
                    caseItem
                  );
                  renderPagination(paginationData, caseItem, targetLinkSelector, bb_right_data);
                });
              }
              let bb_procedure_title = currentProcedure ? currentProcedure.name : '';
              let bb_current_procedure_count = currentProcedure ? currentProcedure.totalCase : '';
              let bb_current_procedure_slug = currentProcedure ? "/" + currentProcedure.slugName : '/';
              let procedure_description = currentProcedure ? currentProcedure.description : '';
              let bb_case_page_title = caseSet.data[0].caseDetails[0].seoHeadline ? caseSet.data[0].caseDetails[0].seoHeadline : linkText;
              let bb_gallery_page_url = "/" + pathSegments[0];
              let schema = {
                "@context": "https://schema.org",
                "@type": "ImageGallery",
                "name": `Before and After Gallery ${bb_procedure_title} : Patient ${bb_current_procedure_count}`,
                "description": `Photo gallery of ${bb_procedure_title} results showing before and after photos from different angles.`,
                "mainEntity": {
                  "@type": "MedicalProcedure",
                  "name": bb_procedure_title,
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
                      "item": window.location.origin
                    },
                    {
                      "@type": "ListItem",
                      "position": 2,
                      "name": seopagetitle,
                      "item": bb_gallery_page_url
                    },
                    {
                      "@type": "ListItem",
                      "position": 3,
                      "name": `Before and After ${bb_procedure_title} Gallery`,
                      "item": bb_current_procedure_slug
                    },
                    {
                      "@type": "ListItem",
                      "position": 4,
                      "name": bb_case_page_title,
                      "item": window.location.href
                    }
                  ]
                },
              };

              let schemaJson = JSON.stringify(schema, null, 4);
              let schemaScript = document.createElement('script');
              schemaScript.type = 'application/ld+json';
              schemaScript.innerHTML = schemaJson;
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
              const filterContentInner = document.querySelector(
                ".bb-filter-content-inner"
              );
              let filter_data = JSON.parse(data.data.filter_data);

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
                  filterContentInner.innerHTML += filterHTML;
                }
              }
              if (filter_data.data.dynamicFilter) {
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
                filterContentInner.innerHTML += filterHTMLA;
              }
              filterContentInner.innerHTML += `<div  id='apply_filter'>
                <button class="apply_bb_filter" onClick='applyFilterBB(${count}, "${pageSlug}", "${elementId}", "${apiToken}", "${websitePropertyId}", "${caseIdentifier}", "${procedureSlug}")'>Apply</button> 
                </div>`;

              handleAdvanceFilter();
            }
          }

          initFavorite();

        } catch (err) {
          console.error("Error parsing case_set JSON:", err);
        }
      })
      .catch((error) => {
        console.error("Error during AJAX request:", error);
      });

  } catch (error) {
    console.error("Error", error)
  }
}

function createFilterData(count, pageSlug, elementId, apiToken, websitePropertyId, caseIdentifier, staticFilterCombine, dynamicFilterCombine) {
  return {
    action: "bb_case_api",
    count,
    pageSlug,
    procedureId: elementId,
    apiToken,
    websitePropertyId,
    caseId: caseIdentifier,
    staticFilter: Object.entries(staticFilterCombine).map(([k, v]) => `&${k}=${v}`).join(""),
    dynamicFilter: Object.entries(dynamicFilterCombine).map(([k, v]) => `&${k}=${v}`).join(""),
    dynamicFilterCombine: Object.keys(dynamicFilterCombine).length ? JSON.stringify(dynamicFilterCombine) : 0,
    ...staticFilterCombine
  };
}

function applyFilterBB(count, pageSlug, elementId, apiToken, websitePropertyId, caseIdentifier, procedureSlug) {
  const contentBox = document.querySelector(".bb-content-boxes");
  // const applyFilterBtn = document.querySelector(".apply_bb_filter");

  contentBox.innerHTML = "";
  // applyFilterBtn.innerHTML = `<img id="apply_bb_filter" src="${bb_plugin_data.heartrunning}" alt="Loading...">`;

  const getCheckedFilters = (selector) => {
    return [...document.querySelectorAll(selector)].reduce((acc, checkbox) => {
      const key = checkbox.getAttribute("data-key").replace(/\s+/g, "|||");
      const value = isNaN(parseInt(checkbox.getAttribute("data-value")))
        ? checkbox.getAttribute("data-value")
        : parseInt(checkbox.getAttribute("data-value"));
      acc[key] = value;
      return acc;
    }, {});
  };

  const staticFilterCombine = getCheckedFilters(".bb-static-filter input[type='checkbox']:checked");
  const dynamicFilterCombine = getCheckedFilters(".bb-dynamic-filter input[type='checkbox']:checked");

  const data = createFilterData(count, pageSlug, elementId, apiToken, websitePropertyId, caseIdentifier, staticFilterCombine, dynamicFilterCombine);


  fetch(bb_plugin_data.ajaxurl, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams(data).toString(),
  })
    .then((response) => response.json())
    .then(({ data }) => {
      if (!data?.case_set) return console.error("Invalid response structure:", data);

      const caseSet = JSON.parse(data.case_set);
      handleLoadMoreButton(caseSet.hasLoadMore);

      let bb_case_count = (count - 1) * 10;
      contentBox.innerHTML = caseSet.data.map(({ photoSets, caseDetails, details = "" }) => {
        if (!photoSets?.length) return "";

        const { highResPostProcessedImageLocation, postProcessedImageLocation, originalBeforeLocation, seoAltText } = photoSets[0];
        const imgSrc = highResPostProcessedImageLocation || postProcessedImageLocation || originalBeforeLocation;
        const imgAlt = seoAltText || "Procedure Image";

        const caseId = caseDetails[0]?.seoSuffixUrl || `bb-case-${bb_case_count++}`;
        const procedureUrl = `/${pageSlug}/${procedureSlug}/${caseId}/`;
        const heartImage = data.bragbook_favorite.includes(caseId) ? bb_plugin_data.heartBordered : bb_plugin_data.heartRed;

        return `
          <div class="bb-content-box">
            <div class="bb-content-thumbnail">
              <a href="${procedureUrl}"><img src="${imgSrc}" alt="${imgAlt}"></a>
              <img class="bb-heart-icon bb-open-fav-modal" data-case-id="${caseId}" data-bb_api_token="${apiToken}" data-bb_website_id="${websitePropertyId}" src="${heartImage}" alt="heart">
            </div>
            <div class="bb-content-box-inner">
              <h5>${procedureSlug} : Patient ${bb_case_count}</h5>
              <p>${details}</p> 
            </div>
            <div class="bb-content-box-cta">
              <a class="view-more-btn" href="${procedureUrl}">View More</a>
            </div>
          </div>
        `;
      }).join("");
    })
    .catch((err) => console.error("Error fetching data:", err));
  // .finally(() => applyFilterBtn.innerHTML = "Apply");
}

function handleAdvanceFilter() {
  document.querySelectorAll(".bb-filter-content-inner-wrapper .accordion")
    .forEach(acc => acc.addEventListener("click", function () {
      this.classList.toggle("active");
      const panel = this.nextElementSibling;
      panel.style.maxHeight = panel.style.maxHeight ? null : panel.scrollHeight + "px";
    }));

  document.getElementById("clearButton")?.addEventListener("click", () => {
    document.querySelectorAll('.bb-checkbox-container input[type="checkbox"]')
      .forEach(checkbox => checkbox.checked = false);

    document.querySelectorAll(".bb-content-box").forEach(box => box.style.display = "block");
  });
}

function handleFilterAndPaginate(count, pageSlug, elementId, apiToken, websitePropertyId,
  caseIdentifier, staticFilter, dynamicFilter, dynamicFilterCombine, staticFilterCombine
) {
  const data = {
    action: "bb_case_api",
    count: count,
    pageSlug: pageSlug,
    procedureId: elementId,
    apiToken: apiToken,
    websitePropertyId: websitePropertyId,
    caseId: caseIdentifier,
    staticFilter: staticFilter,
    dynamicFilter: dynamicFilter,
    dynamicFilterCombine: Object.keys(dynamicFilterCombine).length ? JSON.stringify(dynamicFilterCombine) : 0,
    ...staticFilterCombine
  };
  return data;
}
function generatePagination(caseIds, caseItem) {
  return caseIds.map((item, index) => ({
    id: item.seoSuffixUrl ? item.seoSuffixUrl : `bb-case-${item.id}`,
    caseNumber: index + 1,
    isCurrent: !!(item.seoSuffixUrl ? item.seoSuffixUrl == caseItem.caseDetails[0]?.seoSuffixUrl : item.id == caseItem.id),
  }));
}

function renderPagination(paginationData, caseItem, targetLinkSelector, bb_right_data) {
  let caseSeoId = caseItem.caseDetails[0]?.seoSuffixUrl ? caseItem.caseDetails[0]?.seoSuffixUrl : "bb-case-" + caseItem.id;
  const paginationList = document.getElementById(`pagination-list-${caseItem.id}`);
  if (!paginationList || !paginationData.length) return;

  paginationList.innerHTML = "";
  let baseUrl = window.location.origin + targetLinkSelector;

  const currentPageIndex = paginationData.findIndex(
    (item) => item.id === caseSeoId
  );
  const totalPages = paginationData.length;

  const hasPrevious = currentPageIndex > 0;
  const hasNext = currentPageIndex < totalPages - 1;

  if (hasPrevious) {
    const prevPageId = paginationData[currentPageIndex - 1].id;
    const prevItem = document.createElement("li");
    prevItem.innerHTML = `<a href="${baseUrl}${prevPageId}">Previous</a>`;
    paginationList.appendChild(prevItem);
  }

  let start = Math.max(0, currentPageIndex - 2);
  let end = Math.min(totalPages, start + 4);

  if (end - start < 4) start = Math.max(0, end - 4);

  for (let i = start; i < end; i++) {
    const pageItem = paginationData[i];
    const pageUrl = `${baseUrl}${pageItem.id}`;
    const listItem = document.createElement("li");
    listItem.className = pageItem.id === caseSeoId ? "bb-single-case active" : "bb-single-case";
    listItem.innerHTML = `<a href="${pageUrl}">${pageItem.caseNumber}</a>`;
    paginationList.appendChild(listItem);
  }

  if (hasNext) {
    const nextPageId = paginationData[currentPageIndex + 1].id;
    const nextItem = document.createElement("li");
    nextItem.innerHTML = `<a href="${baseUrl}${nextPageId}">Next</a>`;
    paginationList.appendChild(nextItem);
  }
}



function closeAllPanels() {
  for (let i = 0; i < accordion.length; i++) {
    accordion[i].classList.remove("active");
    let panel = accordion[i].nextElementSibling;
    panel.style.maxHeight = null;
  }
}

for (let i = 0; i < accordion.length; i++) {
  accordion[i].addEventListener("click", function () {
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

function displayProcedureTitle(sidebarData, procedureSlug) {
  const procedureTitle = document.querySelector(`a[href="${window.location.pathname}"]`).innerText.split("(")[0];
  if (document.getElementById("procedure-title")) document.getElementById("procedure-title").innerHTML = `${procedureTitle ? procedureTitle : ""} Before & After Gallery`;
}


function verifyFormData(form) {
  let isValid = true;
  let formDataObject = {};
  let requiredFields = form.querySelectorAll(".bb-is-required");

  requiredFields.forEach(field => {
    let fieldName = field.name;
    let fieldValue = field.value.trim();
    let errorMsg = field.nextElementSibling;
    formDataObject[fieldName] = fieldValue;
    if (!fieldValue) {
      errorMsg.style.display = "block";
      errorMsg.style.color = "#CD2F32";

      isValid = false;
    } else {
      errorMsg.style.display = "none";
    }
  });

  if (formDataObject["email"]) {
    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let emailField = form.querySelector("input[name='email']");
    let emailError = emailField.nextElementSibling;

    if (!emailPattern.test(formDataObject["email"])) {
      emailError.textContent = "Please enter a valid email";
      emailError.style.display = "block";
      emailError.style.color = "#CD2F32";

      isValid = false;
    } else {
      emailError.style.display = "none";
    }
  }
  if (formDataObject["phone"]) {
    let phonePattern = /^\d+$/;
    let phoneField = form.querySelector("input[name='phone']");
    let phoneError = phoneField.nextElementSibling;

    if (!phonePattern.test(formDataObject["phone"])) {
      phoneError.textContent = "Please enter a valid phone number";
      phoneError.style.display = "block";
      phoneError.style.color = "#CD2F32";
      isValid = false;
    } else {
      phoneError.style.display = "none";
    }
  }

  return isValid;
}


function handleConsultationForm() {
  const consultationFormSubmitBtn = document.getElementById("bb-consultation-form-submit");
  const consultationForm = document.getElementById("bb-consultation-form");
  const successMessageElement = document.querySelector(".bb-is-required-success");

  if (!consultationFormSubmitBtn || !consultationForm) return;

  consultationFormSubmitBtn.addEventListener("click", async (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (!verifyFormData(consultationForm)) return;

    const formData = new FormData(consultationForm);
    formData.append("action", "handle_form_submission");

    successMessageElement.textContent = "Submitting form...";
    successMessageElement.style.display = "block";

    try {
      const response = await fetch(bb_plugin_data.ajaxurl, {
        method: "POST",
        body: formData,
      });

      const result = await response.json();
      if (result.success) successMessageElement.textContent = "Thank you, your submission has been received, someone will follow up with your shortly.";
      else successMessageElement.textContent = "Submission failed.";
      consultationForm.style.display = "none";
      successMessageElement.style.fontSize = "1.5em";
    } catch (error) {
      console.error("AJAX Error:", error);
      successMessageElement.textContent = "An error occurred. Please try again.";
    }
  });
}

jQuery(document).ready(function ($) {
  let bb_slider;
  if ($.fn.slick) {
    bb_slider = $("body .bb-slider").slick({
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
        var panel = accordionButton.next(".bb-panel")[0];
        if (panel) {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      }
    });
  }

  var _catTitle = getCatTitleFromUrl();
  if (_catTitle) {
  }

  function highlightMatchingIds() {
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
        $(this).css("color", "#000");
        $(this).css("opacity", "1");
        var accordionButton = $(this)
          .closest(".bb-panel")
          .prev(".bb-accordion");
        accordionButton.addClass("active");
        accordionButton.next(".bb-panel").slideDown();
        var panel = accordionButton.next(".bb-panel")[0];
        if (panel) {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      }
    });
  }

  function getProcedureNameFromUrl(url) {
    var pathname = new URL(url).pathname;
    var pathParts = pathname.split("/").filter(Boolean);

    var procedureSegment =
      pathParts.length > 2
        ? pathParts[pathParts.length - 2]
        : pathParts[pathParts.length - 1];
    var cleanedText = procedureSegment
      ? procedureSegment.replace(/-/g, " ")
      : "";

    return cleanedText;
  }
  var $pagination = $(".pagination");
  var currentPage = $pagination.data("current-page");
  var totalPages = $pagination.data("total-pages");
  function updatePaginationButtons(currentPage, totalPages) {
    $(".load-more-btn.prev").toggle(currentPage > 1);

    $(".load-more-btn.next").toggle(currentPage < totalPages);

    $(".page-number").toggle(totalPages > 1);

    $(".page-number").each(function () {
      var page = parseInt($(this).data("page"));
      $(this).toggle(
        page === currentPage ||
        page === currentPage - 1 ||
        page === currentPage + 1
      );
    });
  }

  updatePaginationButtons(currentPage, totalPages);

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

        $(".page-number.active").removeClass("active");
        $('.page-number[data-page="' + nextPage + '"]').addClass("active");

        $(".load-more-btn").data("page", nextPage);

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

        $(".page-number.active").removeClass("active");
        $this.addClass("active");

        $(".load-more-btn").data("page", currentPage);

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

  $(".bb-sidebar-toggle").on("click", function () {
    $(".bb-sidebar").toggleClass("active");
    $(this).toggleClass("active");
  });

  $("#bragbook_setting_page").on("submit", function (e) {
    e.preventDefault();
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
    var formData = $(this).serialize();

    $.ajax({
      url: bb_plugin_data.ajaxurl,
      type: "POST",
      data: {
        action: "bb_save_bragbook_settings",
        form_data: formData,
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
  $("#bragbook_seeting_form")
    .find('input[type="submit"]')
    .on("click", function (e) {
      e.preventDefault();
      $("#bragbook_seeting_form").trigger("submit");
    });
});

function initFavorite() {
  const modalToggle = Array.from(document.querySelectorAll(".bb-open-fav-modal"));
  let modal = document.querySelector(".bb-fav-modal");
  let modalInner = document.querySelector(".bb-fav-modal-inner");
  let modalCloseIcon = document.querySelector(".bb-fav-modal-close-button");
  let favoriteForm;
  let caseIdInput;
  let bbApiTokenInput;
  let bbWebsiteIdInput;
  if (modalInner && modalInner.querySelector) {
    favoriteForm = modalInner.querySelector("form");
    caseIdInput = modalInner.querySelector("input[name='case-id']");
    bbApiTokenInput = modalInner.querySelector("input[name='api-token']");
    bbWebsiteIdInput = modalInner.querySelector("input[name='website-id']");
  }

  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
  }

  function openModal(caseId, bbApiToken, bbWebsiteId) {
    if (caseIdInput) caseIdInput.value = caseId;
    if (bbApiTokenInput) bbApiTokenInput.value = bbApiToken;
    if (bbWebsiteIdInput) bbWebsiteIdInput.value = bbWebsiteId;

    const encodedCookieValue = getCookie("wordpress_favorite_email");
    if (!encodedCookieValue) {
      fadeIn(modal);
      return;
    }


    let bb_favorite_email = decodeURIComponent(encodedCookieValue);
    let bb_favorite_name = getCookie("wordpress_favorite_name");
    bb_favorite_name = decodeURIComponent(bb_favorite_name);

    let bb_favorite_phone = getCookie("wordpress_favorite_phone");
    bb_favorite_phone = decodeURIComponent(bb_favorite_phone);

    let bb_favorite_case_id = getCookie("wordpress_favorite_case_id");
    bb_favorite_case_id = decodeURIComponent(bb_favorite_case_id);

    let bb_favorite_api_token = getCookie("wordpress_favorite_api_token");
    bb_favorite_api_token = decodeURIComponent(bb_favorite_api_token);

    let bb_favorite_website_id = getCookie("wordpress_favorite_website_id");
    bb_favorite_website_id = decodeURIComponent(bb_favorite_website_id);

    const bb_fav_list_cookie = bb_favorite_case_id.split(",").map(Number);
    const bb_exist_list = new Set(bb_fav_list_cookie);
    const bb_exist = bb_exist_list.has(Number(caseId));
    if (bb_exist) {
      alert("Already favorite!");
      return false;
    }

    const data_cookie = {
      email: bb_favorite_email,
      phone: bb_favorite_phone,
      name: bb_favorite_name,
      caseIds: [caseId],
      bbApiTokens: [bbApiToken],
      bbWebsiteIds: [bbWebsiteId],
    };
    bb_favorites_submission(data_cookie);
  }

  if (modalCloseIcon) modalCloseIcon.addEventListener("click", closeModal);

  function closeModal() {
    fadeOut(modal);
  }

  if (!modalToggle) return;

  modalToggle.forEach((toggle) => {
    toggle.addEventListener("click", (e) => {
      if (isFavoriteListPage) {
        alert("Already favorite!");
        return;
      }
      e.stopPropagation();
      const caseId = toggle.getAttribute("data-case-id");
      const bbApiToken = toggle.getAttribute("data-bb_api_token");
      const bbWebsiteId = toggle.getAttribute("data-bb_website_id");
      openModal(caseId, bbApiToken, bbWebsiteId);
    });
  });

  document.addEventListener("click", (event) => {
    if (
      modal &&
      modal.classList.contains("is-open") &&
      !modalInner.contains(event.target)
    ) {
      closeModal();
    }
  });

  function bb_favorites_submission(data) {
    console.log(data);
    var caseId = data.caseIds;
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
    console.log("response :" , response );

        if (response.success) {
          const imgElements = document.querySelectorAll(`img[data-case-id="${caseId}"]`);
          if (imgElements.length) {
            imgElements.forEach(function (imgElement) {
              imgElement.src = bb_plugin_data.heartBordered;
            });
          }

          const spanElement = document.querySelector("a.bb-sidebar_favorites span");
          if (spanElement) {
            const text = spanElement.textContent;
            const match = text.match(/\((\d+)\)/);

            if (match) {
              spanElement.style.display = "inline";
              spanElement.textContent = `(${response.data.totalFavorites})`;
            }
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

  if (modalInner && modalInner.querySelector) {
    favoriteForm.addEventListener("submit", (event) => {
      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();
      const caseIdInp = caseIdInput.value;
      const bbApiToken = bbApiTokenInput.value;
      const bbWebsiteId = bbWebsiteIdInput.value;
      if (!verifyFormData(favoriteForm)) return;
      const data = {
        name: event.target[0].value,
        email: event.target[1].value,
        phone: event.target[2].value,
        caseIds: [caseIdInp],
        bbApiTokens: [bbApiToken],
        bbWebsiteIds: [bbWebsiteId],
      };
      bb_favorites_submission(data);
    });
  }


  function fadeOut(element) {
    let opacity = 1;
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

}

Array.from(document.querySelectorAll(".bb-filter-select")).forEach((filter) => {
  if (filter) filter.querySelector(".bb-filter-heading")?.addEventListener("click", () => filter?.classList.toggle("active"));
});

Array.from(document.querySelectorAll(".bb-filter-toggle")).forEach((filter) => {
  if (filter) {
    filter.addEventListener("click", () =>
      document.querySelector(".bb-filter-select").classList.toggle("active")
    );
  }
});

if (document.querySelector(".bb-main .bb-form")) {
  let bb_form = document.querySelector(".bb-main .bb-form");
  let bb_inputs = Array.from(document.querySelectorAll(".bb-main .bb-is-required"));
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

    if (!bb_is_required) e.preventDefault()
    else document.querySelector(".bb-is-required-success").style.display = "block";
  });
}

setTimeout(() => {
  const bbrag_modal = document.getElementById("bbrag_modal");
  const bbrag_modalImage = document.getElementById("bbrag_modalImage");
  const bbrag_closeModal = document.querySelector(".bbrag_close");
  const bbrag_prevArrow = document.querySelector(".bbrag_prev");
  const bbrag_nextArrow = document.querySelector(".bbrag_next");

  let bbrag_currentIndex = 0;
  const bbrag_images = document.querySelectorAll(".bbrag_gallery_image");
  function bbrag_openModal(index) {
    bbrag_currentIndex = index;
    bbrag_modal.style.display = "block";
    bbrag_modalImage.src = bbrag_images[bbrag_currentIndex].src;
  }

  function bbrag_closeModalHandler() {
    bbrag_modal.style.display = "none";
  }

  function bbrag_showNextImage() {
    bbrag_currentIndex = (bbrag_currentIndex + 1) % bbrag_images.length;
    bbrag_modalImage.src = bbrag_images[bbrag_currentIndex].src;
  }

  function bbrag_showPrevImage() {
    bbrag_currentIndex =
      (bbrag_currentIndex - 1 + bbrag_images.length) % bbrag_images.length;
    bbrag_modalImage.src = bbrag_images[bbrag_currentIndex].src;
  }

  bbrag_images.forEach((img, index) => {
    img.addEventListener("click", () => bbrag_openModal(index));
  });

  if (bbrag_closeModal) bbrag_closeModal.addEventListener("click", bbrag_closeModalHandler);

  if (bbrag_prevArrow) bbrag_prevArrow.addEventListener("click", bbrag_showPrevImage);

  if (bbrag_nextArrow) bbrag_nextArrow.addEventListener("click", bbrag_showNextImage);

  window.addEventListener("click", (event) => { if (event.target === bbrag_modal) bbrag_closeModalHandler() });
}, 2000);

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById('popup').style.visibility = 'visible';
  document.getElementById('popup').style.opacity = '1';
});

function closePopup() {
  document.getElementById('popup').style.visibility = 'hidden';
  document.getElementById('popup').style.opacity = '0';
}

function leavePopup() {
  let currentUrl = window.location.href;
  let baseUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/', currentUrl.lastIndexOf('/') - 1));
  window.location.href = baseUrl;
}

document.addEventListener("DOMContentLoaded", function () {
  let header = document.querySelector("header");
  if (!header) header = document.querySelector(".header");
  const banner = document.querySelector(".bb-main");
  const mainPage = document.querySelector("#page");

  if (window.getComputedStyle(header).position === "fixed") {
    const headerHeight = header.offsetHeight;
    banner.style.paddingTop = `${headerHeight + 30}px`;
    if (mainPage) mainPage.style.paddingTop = `${headerHeight + 30}px`;
  }
});

document.addEventListener("DOMContentLoaded", function () {
  let headerSection = document.querySelector("header");
  if (!headerSection) headerSection = document.querySelector(".header");
  const modalBox = document.querySelector(".bb-fav-modal");
  const headerHeight = headerSection.offsetHeight;

  if (headerSection && modalBox) {
    if (window.getComputedStyle(headerSection).position === "fixed") {
      modalBox.style.height = `calc(100vh - ${headerHeight}px)`;
    } else {
      modalBox.style.height = `100vh`;
      modalBox.style.paddingTop = `${headerHeight + 50}px`;
      window.addEventListener('scroll', function () {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        if (scrollTop > 100) {
          modalBox.style.transition = 'all 0.3s';
          modalBox.style.paddingTop = '50px';
        } else {
          modalBox.style.paddingTop = `${headerHeight + 50}px`;
        }
      });
    }
  } else {
    console.warn("'.header' or '.bb-fav-modal' element not found.");
  }
});