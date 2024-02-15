var chatbotUri = document.currentScript.dataset.chatboturi;
window.addEventListener('load', () => {

    const history = [];

    document.querySelector(".chatbox .chatbox-history").style.maxHeight = (window.innerHeight / 2) + "px";

    document.querySelector(".chatbox .chatbox-form").addEventListener('submit', (e) => {
        e.preventDefault();

        let message = document.querySelector(".message").value;
        if (message) {
            document.querySelector(".send-message").disabled = true;

            let date = new Date();

            document.querySelector(".chatbox-history-content").innerHTML += ` <div class="question question-${date.valueOf()}">
                <span class="chatbox-datetime">${date.getHours() + ":" + date.getMinutes()} </span>
                ${message}
            </div>`;
            document.querySelector(".chatbox .question-" + date.valueOf()).scrollIntoView();

            document.querySelector(".message").value = "";

            fetch(chatbotUri, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({"message": message, history: history}),
            })
            .then(response => response.json())
            .then(data => {
                let date = new Date();

                history.push({role: "user", content: data.question});
                history.push({role: "assistant", content: data.answer});
                document.querySelector(".chatbox-history-content").innerHTML += `<div class="answer answer-${date.valueOf()}">
                    <span class="chatbox-datetime">${date.getHours() + ":" + date.getMinutes()}</span>
                    ${data.answer.replace("\n", "<br>")}
                </div>`;
                document.querySelector(".chatbox .answer-" + date.valueOf()).scrollIntoView();
            })
            .catch((error) => {
                console.error('Error:', error);
            }).finally(() => {
                document.querySelector(".send-message").disabled = false;
            });
        }
    });

    document.querySelector(".chatbox .clear").addEventListener('click', () => {
        document.querySelector(".chatbox-history-content").innerHTML = "";
    });
    document.querySelector(".chatbox .minimize").addEventListener('click', () => {
        document.querySelector(".chatbox").classList.toggle("minimize");
    });
    document.querySelector(".chatbox .maximize").addEventListener('click', () => {
        document.querySelector(".chatbox").classList.toggle("minimize");
    });
});