<template>
    <div>
        <label for="context">Application context <span class="panel-heading">Start typing in textbox below for suggestions!</span></label>
        <input id="context" name="context" type="text" placeholder="what are you looking for?" v-model="query" v-on:keyup="autoComplete" class="form-control" autocomplete="off">
        <div v-if="results.length">
            <ul class="list-group" v-bind:style="{ display: ulDisplay }">
                <li class="list-group-item"
                    style="cursor: pointer"
                    v-for="result in results"
                    @click="suggestionClick(result)">
                    {{ result.context }}
                </li>
            </ul>
        </div>
    </div>
</template>


<script>
    export default {
        data() {
            return {
                query: '',
                selected: '',
                ulDisplay: 'none',
                results: []
            }
        },

        methods: {
            autoComplete() {
                this.results = [];
                if (this.query.length > 2) {
                    axios.get('/suggestions/search-context', {params: {query: this.query}}).then(response => {
                        this.ulDisplay = 'block';
                        this.results = response.data;
                    });
                }
            },
            suggestionClick(res) {
                this.query = res.context;
                this.ulDisplay = 'none';
            },
        }
    }
</script>
