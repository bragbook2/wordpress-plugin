let loadMoreCount = 1;

document.addEventListener("DOMContentLoaded", () => {
  fetchCaseData(loadMoreCount);
  handleLoadMoreButton();
  handleFilterToggle();
  handleSingleClickToggles();
  handleMultipleClickToggles();
});

function handleLoadMoreButton() {
  const loadMoreContainer = document.querySelector(".ajax-load-more");
  if (!loadMoreContainer) return;

  const loadMoreButton = loadMoreContainer.querySelector(".bb_ajax-load-more-btn");
  if (!loadMoreButton) return;

  const currentOffset = parseInt(loadMoreButton.getAttribute("data-offset"), 10);
  if (currentOffset === 1) loadMoreButton.style.display = "none";
}

function handleFilterToggle() {
  if (filterBtn) {
    filterBtn.addEventListener("click", () => {
      filterContent.classList.toggle("active");
    });
  }
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

function fetchCaseData(loadMoreCount) {
  try {
    let count = loadMoreCount;
    const pathSegments = window.location.pathname.split("/").filter(Boolean);

    const isCarouselPage = pathSegments.length === 1;
    const isListsPage = pathSegments.length === 2;
    const isViewMoreDetailPage = pathSegments.length === 3;
    const isFavoriteListPage = pathSegments[1] === "favorites" && pathSegments.length === 2;
    const currentPage = { isCarouselPage, isListsPage, isViewMoreDetailPage, isFavoriteListPage }

    const pageSlug = pathSegments[0] || "";
    const procedureSlug = pathSegments[1] || "";
    const caseIdentifier = pathSegments[2]?.includes("bb-case") ? pathSegments[2].split("-").pop() : "";
    let seoSuffixUrl = caseIdentifier ? "" : pathSegments[2] || "";

    const targetLinkSelector = `/${pageSlug}/${procedureSlug}/`;
    const targetLinkElement = document.querySelector(`a[href="${targetLinkSelector}"]`);

    const elementId = targetLinkElement?.id || "";
    const apiToken = targetLinkElement?.getAttribute("data-api-token");
    const websitePropertyId = targetLinkElement?.getAttribute("data-website-property-id");
    let linkText;
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
      currentPage: JSON.stringify(currentPage)
    };

    fetch(bb_plugin_data.ajaxurl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded", },
      body: new URLSearchParams(requestData).toString(),
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("Ressponse => ", data);
        try {
          let heartImage;
          let sidebarApi;
          if (data.data.sidebar_api) {
            try {
              const parsedSidebarApi = JSON.parse(JSON.parse(data.data.sidebar_api));
              sidebarApi = parsedSidebarApi.data?.flatMap(item => item.procedures) || [];
            } catch (error) {
              console.error("Error parsing sidebar_api:", error);
              sidebarApi = [];
            }
          }

          if (data.data.bragbook_favorite.length > 0) {
            var fav_data = data.data.bragbook_favorite;
            let element_fav_count = document.getElementById("bb_favorite_caseIds_count");
            element_fav_count.textContent = "(" + `${fav_data.length}` + ")";
          }
          if (data.data && data.data.case_set) {
            let caseSet = JSON.parse(data.data.case_set);
            if (caseIdentifier == "" && !seoSuffixUrl) {
              if (isListsPage && !isFavoriteListPage) {
                document.querySelector(".bb_ajax-load-more-btn").innerHTML = "Load More";
                if (!(!!caseSet.hasLoadMore)) document.getElementById("load-container").style.display = "none"
              }

              var bb_case_count = (count - 1) * 10;
              let contentBox = document.querySelector(".bb-content-boxes");
              document.querySelector("#bb_ajax-load-more-btn")?.remove();

              const applyBBButton = document.querySelector(".apply_bb_filter");
              if (applyBBButton) applyBBButton.innerHTML = `Apply`;
              let images = [];
              if (caseSet.data) {
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
                    let caseId = "";
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
                      "name": procedureSlug,
                      "description": `Photo gallery of ${procedureSlug} results showing before and after photos from different angles.`,
                      "url": `${targetLinkSelector}${caseItem.caseId}`,
                      "thumbnailUrl": imgSrc
                    };

                    let proceduralName = "";
                    if (caseItem.caseDetails[0].seoHeadline) {
                      proceduralName = caseItem.caseDetails[0].seoHeadline
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
                  "name": `${procedureSlug} Before & After Gallery`,
                  "description": `Review ${caseSet.data.length} ${procedureSlug} before and after cases. Each case includes photos from multiple angles, along with details about the procedure.`,
                  "url": `/${pageSlug}/${procedureSlug}/`,
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
                        "name": `${procedureSlug}`,
                        "item": `/${pageSlug}/${procedureSlug}/`
                      },
                      {
                        "@type": "ListItem",
                        "position": 3,
                        "name": `${procedureSlug} Procedure`,
                        "item": `/${pageSlug}/${procedureSlug}/`
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
                      const isCombine = sidebarApi[0].ids;
                      let slugName;
                      if (isCombine) {
                        const totalTokens = 2;
                        for (let i = 0; i < totalTokens; i++) {
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
                                            data-case-id="${caseId}"
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
                  if (caseId == caseIdentifier || seoSuffixUrl == caseItem.caseDetails[0]?.seoSuffixUrl) {
                    if (caseItem.photoSets && caseItem.photoSets.length > 0) {
                      caseItem.photoSets.forEach((value, itemIndex) => {
                        let bb_new_image_value =
                          value.highResPostProcessedImageLocation ??
                          value.postProcessedImageLocation ??
                          value.originalBeforeLocation;

                        let imgElement = document.createElement("img");
                        imgElement.className =
                          "bbrag_gallery_image testing-image";
                        imgElement.src = bb_new_image_value;
                        imgElement.alt = value.seoAltText + " - angle " + (itemIndex + 1) ?? "Procedure Image";
                        patienLeftBox.appendChild(imgElement);
                        let imageObjc = {
                          "@type": "ImageObject",
                          "name": procedureSlug,
                          "description": `Photo gallery of ${procedureSlug} results showing before and after photos from different angles.`,
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
                  console.log("Case Details =>", caseItem);

                  let title_suffix = document.title.split(' - ')[1];

                  document.title = caseItem.caseDetails[0]?.seoPageTitle ? caseItem.caseDetails[0]?.seoPageTitle + (title_suffix ? (" - " + title_suffix) : "") : document.title
                  let bb_right_data = `
                        <div class="bb-patient-row">
                            <h2>${caseItem.caseDetails[0]?.seoHeadline || linkText}</h2>
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
                    caseItem
                  );
                  renderPagination(paginationData, caseItem, targetLinkSelector);
                });
              }
              let bb_case_url_title = 'Cosmetic Procedure';
              let bb_current_case_page_count = '';
              let default_and_seo_page_title = "Procedure Title";
              let procedure_description = "Detailed description of the procedure.";
              let bb_gallery_page_title = "Gallery Page Title";
              let case_page_title = "Case Page Title";
              let bbrag_case_url = "/case-gallery";
              let bb_pro_cat_page = "/category-page";
              let targetLinkSelectorc = "/target-url/";
              let caseIdentifierc = "case-id";

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
              let filterContent = document.querySelector(
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
                  filterContent.innerHTML += filterHTML;
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
                filterContent.innerHTML += filterHTMLA;
              }
              filterContent.innerHTML += `<div  id='apply_filter'>
                <button class="apply_bb_filter" onClick='applyFilterBB(${count}, "${pageSlug}", "${elementId}", "${apiToken}", "${websitePropertyId}", "${caseIdentifier}", "${procedureSlug}")'>Apply</button> 
                </div>`;

              bb_advance_filter();
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
function handleDynamicCheckboxChange(key, value) {
  const checkboxes = document.querySelectorAll(`input[name=${key}]`);

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", (event) => {
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
  pageSlug,
  elementId,
  apiToken,
  websitePropertyId,
  caseIdentifier,
  procedureSlug
) {
  document.querySelector(".bb-content-boxes").innerHTML = "";
  document.querySelector(
    ".apply_bb_filter"
  ).innerHTML = `<img id="apply_bb_filter" src="${bb_plugin_data.heartrunning}" alt="Loading...">`;
  let dynamicFilterBB = document.querySelectorAll(
    '.bb-dynamic-filter input[type="checkbox"]:checked'
  );
  let staticFilterBB = document.querySelectorAll(
    '.bb-static-filter input[type="checkbox"]:checked'
  );
  let staticFilterCombine = {};
  let staticFilter = "";
  let dynamicFilter = "";
  let dynamicFilterCombine = {};

  staticFilterBB.forEach((checkbox) => {
    let dataKey = checkbox.getAttribute("data-key");
    let dataValue = checkbox.getAttribute("data-value");
    staticFilter += `&${dataKey}=${dataValue}`;
    dataValue = isNaN(parseInt(dataValue)) ? `"${dataValue}"` : parseInt(dataValue);
    staticFilterCombine[dataKey] = dataValue;
  });



  dynamicFilterBB.forEach((checkbox) => {
    let dataKey = checkbox.getAttribute("data-key");
    let dataValue = checkbox.getAttribute("data-value");
    dataKey = dataKey.replace(/\s+/g, "|||");
    dynamicFilter += `&${dataKey}=${dataValue}`;
    dataValue = isNaN(parseInt(dataValue)) ? `${dataValue}` : parseInt(dataValue);
    dynamicFilterCombine[dataKey] = dataValue;
  });
  let data = filter_and_paginate(
    count,
    pageSlug,
    elementId,
    apiToken,
    websitePropertyId,
    caseIdentifier,
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
          if (caseIdentifier == "") {
            var bb_case_count = (count - 1) * 10;
            let contentBox = document.querySelector(".bb-content-boxes");
            document.querySelector(".bb_ajax-load-more-btn").innerHTML = "Load More";
            if (!(!!caseSet.hasLoadMore)) document.getElementById("load-container").style.display = "none"

            caseSet.data.forEach((caseItem) => {
              if (caseItem.photoSets && caseItem.photoSets.length > 0) {
                let photoSet = caseItem.photoSets[0];
                let imgSrc =
                  photoSet.highResPostProcessedImageLocation ||
                  photoSet.postProcessedImageLocation ||
                  photoSet.originalBeforeLocation;
                let imgAlt = photoSet.seoAltText || "Procedure Image";
                let caseId = "";
                seoSuffixUrl = caseItem.caseDetails[0].seoSuffixUrl;
                if (seoSuffixUrl) {
                  caseId = seoSuffixUrl;
                } else {
                  caseId = "bb-case-" + caseItemId;
                }

                if (fav_data.includes(caseId)) {
                  heartImage = bb_plugin_data.heartBordered;
                } else {
                  heartImage = bb_plugin_data.heartRed;
                }
                let caseDetails = caseItem.details || "";
                caseItem.patientCount = ++bb_case_count;
                let procedureUrl = `/${pageSlug}/${procedureSlug}/${caseId}/`;

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
                                        <h5>${procedureSlug} : Patient ${caseItem.patientCount}</h5>
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
  pageSlug,
  elementId,
  apiToken,
  websitePropertyId,
  caseIdentifier,
  staticFilter,
  dynamicFilter,
  dynamicFilterCombine,
  staticFilterCombine
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

function renderPagination(paginationData, caseItem, targetLinkSelector) {
  let caseSeoId = caseItem.caseDetails[0]?.seoSuffixUrl ? caseItem.caseDetails[0]?.seoSuffixUrl : caseItem.id;
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

let filterBtn = document.querySelector(".bb-filter-heading");
let filterContent = document.querySelector(".bb-filter-content");
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



  $(".bb-form").submit(function (event) {

    event.preventDefault();


    var formData = $(this).serialize();


    $.ajax({
      type: "POST",
      url: bb_plugin_data.ajaxurl,
      data: formData + "&action=handle_form_submission",
      beforeSend: function () {
        $(".bb-is-required-success").text("Submitting form...");
      },
      success: function (response) {
        var successMessage = response.data;
        $(".bb-is-required-success").text(successMessage);

      },
      error: function (xhr, status, error) {

        $(".bb-is-required-success").text(error);
      },
    });
  });

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
  modal = document.querySelector(".bb-fav-modal");
  modalInner = document.querySelector(".bb-fav-modal-inner");
  if (modalInner && modalInner.querySelector) {
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

  function openModal(caseId, bbApiToken, bbWebsiteId) {
    if (caseIdInput) {
      caseIdInput.value = caseId;
    }
    if (bbApiTokenInput) {
      bbApiTokenInput.value = bbApiToken;
    }
    if (bbWebsiteIdInput) {
      bbWebsiteIdInput.value = bbWebsiteId;
    }

    var encodedCookieValue = getCookie("wordpress_favorite_email");
    if (encodedCookieValue !== undefined) {
      var bb_favorite_email = decodeURIComponent(encodedCookieValue);
      var bb_favorite_name = getCookie("wordpress_favorite_name");
      var bb_favorite_name = decodeURIComponent(bb_favorite_name);

      var bb_favorite_phone = getCookie("wordpress_favorite_phone");
      var bb_favorite_phone = decodeURIComponent(bb_favorite_phone);

      var bb_favorite_case_id = getCookie("wordpress_favorite_case_id");
      var bb_favorite_case_id = decodeURIComponent(bb_favorite_case_id);
      var caseId = Number(caseId);

      var bb_favorite_api_token = getCookie("wordpress_favorite_api_token");
      var bb_favorite_api_token = decodeURIComponent(bb_favorite_api_token);

      var bb_favorite_website_id = getCookie("wordpress_favorite_website_id");
      var bb_favorite_website_id = decodeURIComponent(bb_favorite_website_id);

      var bb_fav_list_cookie = bb_favorite_case_id.split(",").map(Number);
      var bb_exist_list = new Set(bb_fav_list_cookie);
      var bb_exist = bb_exist_list.has(caseId);

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

  function closeModal() {
    fadeOut(modal);
  }

  if (modalToggle) {
    modalToggle.forEach((toggle) => {
      toggle.addEventListener("click", (e) => {
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
          if (response.success) {
            var imgElement = jQuery(`img[data-case-id="${caseId}"]`);
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

              $span.text("(" + newValue + ")");
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
      form.addEventListener("submit", (e) => {
        e.preventDefault();

        const caseId = caseIdInput.value;
        const bbApiToken = bbApiTokenInput.value;
        const bbWebsiteId = bbWebsiteIdInput.value;
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
