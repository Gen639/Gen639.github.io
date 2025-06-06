document.addEventListener("DOMContentLoaded", () => {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "xml/news.xml", true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      const xml = xhr.responseXML;
      const items = xml.getElementsByTagName("item");
      const container = document.getElementById("news-container");

      for (let i = 0; i < items.length; i++) {
        const title = items[i].getElementsByTagName("title")[0].textContent;
        const date = items[i].getElementsByTagName("date")[0].textContent;
        const description =
          items[i].getElementsByTagName("description")[0].textContent;

        const card = document.createElement("div");
        card.classList.add("news-card");
        card.innerHTML = `
      
          <div class="news-content">
            <h3>${title}</h3>
            <p class="news-date">${date}</p>
            <p>${description}</p>
          </div>
        `;
        container.appendChild(card);
      }
    }
  };
  xhr.send();
});
