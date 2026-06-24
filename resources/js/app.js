import "./bootstrap";
import * as FilePond from "filepond";
import Choices from "choices.js";
import "filepond/dist/filepond.min.css";
import { Html5Qrcode, Html5QrcodeScanner } from "html5-qrcode";
// Import Tabulator
import { TabulatorFull as Tabulator } from "tabulator-tables";
import Sortable from "sortablejs";
import html2canvas from "html2canvas";
// TinyMCE
import tinymce from "tinymce/tinymce";
import "tinymce/skins/ui/oxide/skin.min.css";
import "tinymce/icons/default/icons";
import "tinymce/themes/silver/theme";
import "tinymce/plugins/link";
import "tinymce/plugins/table";
import "tinymce/plugins/image";
import "tinymce/plugins/code";
import "tinymce/plugins/lists";
import "tinymce/plugins/advlist";
import "tinymce/plugins/autolink";
import "tinymce/plugins/wordcount";
import "tinymce/models/dom/model";


window.Html5Qrcode = Html5Qrcode;
window.Html5QrcodeScanner = Html5QrcodeScanner;
//window.Alpine = Alpine;
window.FilePond = FilePond;
window.Choices = Choices;
//Alpine.start();

// Make html2canvas available globally
window.html2canvas = html2canvas;
window.tinymce = tinymce;

// Make Tabulator available globally
window.Tabulator = Tabulator;

//Import SortableJS
window.Sortable = Sortable;
console.log("*** Loaded SortableJS ***");

//Default FilePond configuration
const inputElement = document.querySelector("input[type=\"file\"].filepond");
const csrfToken = document
    .querySelector("meta[name=\"csrf-token\"]")
    .getAttribute("content");
FilePond.create(inputElement).setOptions({});

// Filepond configuration with CSRF token
const inputElementFileUpload = document.querySelector("input.fileupload");
FilePond.create(inputElementFileUpload, {
    server: {
        url: "/upload",
        process: {
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector("meta[name=\"csrf-token\"]")
                    .getAttribute("content")
            }
        }
    }
});
window.addEventListener("DOMContentLoaded", () => {
    // Initialize Tabulator tables if they exist
    const tabulatorTables = document.querySelectorAll(".tabulator");
    tabulatorTables.forEach((table) => {
        // You can add default Tabulator options here if needed
        new Tabulator(table, {
            // Default options
        });
    });

    // Initialize TinyMCE only when the DOM is fully loaded
    if (document.querySelector("textarea.tinymce-editor")) {
        tinymce.init({
            selector: "textarea.tinymce-editor",
            base_url: "/vendor/tinymce",
            plugins: "link table image code lists",
            toolbar:
                "undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | code",
            height: 500,
            skin: false,
            content_css: false,
            promotion: false,
            setup: function(editor) {
                editor.on("init", function(e) {
                    console.log("TinyMCE initialized");
                });
            }
        });
    }
});
