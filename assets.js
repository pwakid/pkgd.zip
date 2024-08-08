document.addEventListener('DOMContentLoaded', () => {
    let keySequence = [];
    const hotkeySequence = ['x', 'z'];
    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey) {
            keySequence.push(event.key.toLowerCase());
            if (keySequence.length > 2) {
                keySequence.shift();
            }
            if (keySequence.join('') === hotkeySequence.join('')) {
                alert('Hotkey Ctrl+XZ triggered!');
                keySequence = [];
            }
        } else {
            keySequence = [];
        }
    });
});


function loadFileContent(fileName) {
    if (fileName === "") {
        document.getElementById("fileContent").value = "";
        return;
    }
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "load_file.php?file=" + encodeURIComponent(fileName), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById("fileContent").value = xhr.responseText;
            decodeHtmlEntities();
        }
    };
    xhr.send();
}

function decodeHtmlEntities() {
    const textarea = document.getElementById("fileContent");
    const decodedValue = textarea.value
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'");
    textarea.value = decodedValue;
}
