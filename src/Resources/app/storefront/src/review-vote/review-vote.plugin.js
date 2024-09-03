import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class ReviewVotePlugin extends Plugin {
    init() {
        this._client = new HttpClient();

        const forms = document.querySelectorAll('[data-review-vote-form]');

        forms.forEach((form) => {
            form.addEventListener('submit', () => {
                event.preventDefault()

                const submitButton = event.submitter;

                const formData = new FormData(form);

                if (submitButton) {
                    formData.append(submitButton.name, submitButton.value);
                }

                const formDataObj = {};
                formData.forEach((value, key) => {
                    formDataObj[key] = value;
                });

                this._client.post(
                    form.action,
                    formData,
                    (res) =>
                    {
                        res = JSON.parse(res);

                        const upvoteButton = form.querySelector('button[value="upvote"]')
                        const downvoteButton = form.querySelector('button[value="downvote"]')

                        upvoteButton.children[0].innerHTML = res.upvotes;
                        if(res.customerVoteType === "upvote") {
                            upvoteButton.classList.value = upvoteButton.dataset.classActive
                        } else {
                            upvoteButton.classList.value = upvoteButton.dataset.classInactive
                        }

                        downvoteButton.children[0].innerHTML = res.downvotes;
                        if(res.customerVoteType === "downvote") {
                            downvoteButton.classList.value = downvoteButton.dataset.classActive
                        } else {
                            downvoteButton.classList.value = downvoteButton.dataset.classInactive
                        }

                    }
                )
            })
        })
    }
}