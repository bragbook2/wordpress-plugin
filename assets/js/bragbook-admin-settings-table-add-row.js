
document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.querySelector("#dynamicTable tbody");
    const ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";

    const getLastRowNumber = () => {
        const rows = [...tableBody.querySelectorAll("tr")];
        return rows.reduce((max, row) => {
            const input = row.querySelector('input[data-key^="page_"]');
            if (input) {
                const num = parseInt(input.dataset.key.split('_')[1], 10);
                return Math.max(max, num);
            }
            return max;
        }, 0);
    };

    const createInputCell = (name, rowNumber) => {
        const td = document.createElement("td");
        const input = document.createElement("input");
        input.type = "text";
        input.setAttribute("data-key", `page_${rowNumber}`);
        input.setAttribute("name", `${name}[page_${rowNumber}]`);
        input.required = true;
        td.appendChild(input);
        return td;
    };

    const createButtonCell = () => {
        const td = document.createElement("td");

        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className = "button removeRow";
        removeBtn.textContent = "Remove Row";

        const addBtn = document.createElement("button");
        addBtn.type = "button";
        addBtn.className = "button addRow";
        addBtn.textContent = "Add Row";

        td.appendChild(removeBtn);
        td.appendChild(addBtn);
        return td;
    };

    const addRow = () => {
        const newRowNumber = getLastRowNumber() + 1;
        const row = document.createElement("tr");

        row.appendChild(createInputCell("bragbook_api_token", newRowNumber));
        row.appendChild(createInputCell("bragbook_websiteproperty_id", newRowNumber));
        row.appendChild(createInputCell("bb_gallery_page_slug", newRowNumber));
        row.appendChild(createInputCell("bb_seo_page_title", newRowNumber));
        row.appendChild(createInputCell("bb_seo_page_description", newRowNumber));
        row.appendChild(createButtonCell());

        tableBody.appendChild(row);
        updateButtonVisibility();
    };

    const removeRow = (row) => {
        if (tableBody.rows.length <= 1) return;

        const input = row.querySelector('input[data-key^="page_"]');
        const bb_remove_id = input ? input.dataset.key : '';

        if (bb_remove_id) {
            jQuery.post(ajaxUrl, {
                action: 'bb_setting_remove_row',
                bb_remove_id
            });
        }

        row.remove();
        updateButtonVisibility();
    };

    const updateButtonVisibility = () => {
        const rows = tableBody.querySelectorAll("tr");
        rows.forEach((row, index) => {
            const addBtn = row.querySelector(".addRow");
            const removeBtn = row.querySelector(".removeRow");

            if (addBtn) addBtn.style.display = (index === rows.length - 1) ? "inline-block" : "none";
            if (removeBtn) removeBtn.style.display = (rows.length > 1) ? "inline-block" : "none";
        });
    };

    tableBody.addEventListener("click", (event) => {
        const row = event.target.closest("tr");
        if (event.target.classList.contains("addRow")) {
            addRow();
        }
        if (event.target.classList.contains("removeRow") && row) {
            removeRow(row);
        }
    });

    updateButtonVisibility();

    // Toggle combine gallery slug input visibility
    const createBtn = document.getElementById("createCombineGallery");
    const slugContainer = document.getElementById("slugFieldContainer");
    const slugInput = document.querySelector(".combineGallerySlug");

    if (createBtn && slugContainer && slugInput) {
        if (slugInput.value === "") {
            createBtn.style.display = "block";
            slugContainer.style.display = "none";

            createBtn.addEventListener("click", () => {
                slugContainer.style.display = (slugContainer.style.display === "none") ? "block" : "none";
            });
        } else {
            createBtn.style.display = "none";
            slugContainer.style.display = "block";
        }
    }
});
