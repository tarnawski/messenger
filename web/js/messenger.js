const BASIC_URL = 'http://localhost';
const WEBSOCKET_URL = 'ws://0.0.0.0:9502';

history();
liveFeed();

function history()
{
    fetch(BASIC_URL).then(response => response.json()).then(response => {
        response.forEach(item => document.getElementById('board').append(card(item.content, item.created_at)));
    });
}

function liveFeed()
{
    (new WebSocket(WEBSOCKET_URL)).onmessage = function (event) {
        document.getElementById('board').prepend(card(JSON.parse(event.data).content, JSON.parse(event.data).created_at));
    };
}

function card(content, date)
{
    let card = document.createElement('div');
    card.className = 'card mt-3';

    let body = document.createElement('div');
    body.className = 'card-body';

    let text = document.createElement('p');
    text.className = 'card-text';
    text.innerText = content;

    let footer = document.createElement('div');
    footer.className = 'card-footer text-center text-muted';
    footer.innerText = date;

    card.append(body);
    body.append(text)
    card.append(footer);

    return card;
}
