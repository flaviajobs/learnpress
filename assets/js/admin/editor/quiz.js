!function(t){var e={};function n(i){if(e[i])return e[i].exports;var o=e[i]={i:i,l:!1,exports:{}};return t[i].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=t,n.c=e,n.d=function(t,e,i){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:i})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var i=Object.create(null);if(n.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)n.d(i,o,function(e){return t[e]}.bind(null,o));return i},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=29)}({29:function(t,e,n){"use strict";n.r(e);var i={heartbeat:function(t){return t.heartbeat},questionTypes:function(t){return t.types},defaultNewQuestionType:function(t){return t.default_new},action:function(t){return t.action},id:function(t){return t.quiz_id},status:function(t){return t.status||"error"},currentRequest:function(t){return t.countCurrentRequest||0},nonce:function(t){return t.nonce}},o={UPDATE_HEART_BEAT:function(t,e){t.heartbeat=!!e},UPDATE_STATUS:function(t,e){t.status=e},UPDATE_NEW_QUESTION_TYPE:function(t,e){t.default_new=e},INCREASE_NUMBER_REQUEST:function(t){t.countCurrentRequest++},DECREASE_NUMBER_REQUEST:function(t){t.countCurrentRequest--}},s={heartbeat:function(t){LP.Request({type:"heartbeat"}).then((function(e){var n=e.body;t.commit("UPDATE_HEART_BEAT",!!n.success)}),(function(e){t.commit("UPDATE_HEART_BEAT",!1)}))},newRequest:function(t){t.commit("INCREASE_NUMBER_REQUEST"),t.commit("UPDATE_STATUS","loading"),window.onbeforeunload=function(){return""}},requestCompleted:function(t,e){t.commit("DECREASE_NUMBER_REQUEST"),0===t.getters.currentRequest&&(t.commit("UPDATE_STATUS",e),window.onbeforeunload=null)}},u={status:function(t){return t.status},pagination:function(t){return t.pagination},items:function(t,e){return t.items.map((function(t){var n=e.addedItems.find((function(e){return t.id===e.id}));return t.added=!!n,t}))},code:function(t){return Date.now()},addedItems:function(t){return t.addedItems},isOpen:function(t){return t.open},quiz:function(t){return t.quizId}},r={TOGGLE:function(t){t.open=!t.open},SET_QUIZ:function(t,e){t.quizId=e},SET_LIST_ITEMS:function(t,e){t.items=e},ADD_ITEM:function(t,e){t.addedItems.push(e)},REMOVE_ADDED_ITEM:function(t,e){t.addedItems.forEach((function(n,i){n.id===e.id&&t.addedItems.splice(i,1)}))},RESET:function(t){t.addedItems=[],t.items=[]},UPDATE_PAGINATION:function(t,e){t.pagination=e},SEARCH_ITEM_REQUEST:function(t){t.status="loading"},SEARCH_ITEM_SUCCESS:function(t){t.status="successful"},SEARCH_ITEM_FAIL:function(t){t.status="fail"}},c={toggle:function(t){t.commit("TOGGLE")},open:function(t,e){t.commit("SET_QUIZ",e),t.commit("RESET"),t.commit("TOGGLE")},searchItems:function(t,e){t.commit("SEARCH_ITEM_REQUEST"),LP.Request({type:"search-items",query:e.query,page:e.page,exclude:JSON.stringify([])}).then((function(e){var n=e.body;if(n.success){var i=n.data;t.commit("SET_LIST_ITEMS",i.items),t.commit("UPDATE_PAGINATION",i.pagination),t.commit("SEARCH_ITEM_SUCCESS")}}),(function(e){t.commit("SEARCH_ITEMS_FAIL"),console.log(e)}))},addItem:function(t,e){t.commit("ADD_ITEM",e)},removeItem:function(t,e){t.commit("REMOVE_ADDED_ITEM",e)},addQuestionsToQuiz:function(t,e){var n=t.getters.addedItems;n.length>0&&LP.Request({type:"add-questions-to-quiz",items:JSON.stringify(n),draft_quiz:JSON.stringify(e)}).then((function(e){var n=e.body;if(n.success){var i=n.data;t.commit("lqs/SET_QUESTIONS",i,{root:!0}),t.commit("TOGGLE")}}),(function(t){console.log(t)}))}},d=window.jQuery,a=function(t){return{namespaced:!0,state:d.extend({quizId:!1,pagination:"",status:""},t.chooseItems),getters:u,mutations:r,actions:c}},E=n(8),_={listQuestions:function(t){return t.questions||[]},questionsOrder:function(t){return t.order||[]},externalComponent:function(t){return t.externalComponent||[]},supportAnswerOptions:function(t){return t.supportAnswerOptions||[]},hiddenQuestionsSettings:function(t){return t.hidden_questions_settings||[]},hiddenQuestions:function(t){return t.questions.filter((function(t){return!t.open})).map((function(t){return parseInt(t.id)}))},isHiddenListQuestions:function(t,e){var n=e.listQuestions,i=e.hiddenQuestions;return n.length===i.length},disableUpdateList:function(t){return t.disableUpdateList},statusUpdateQuestions:function(t){return t.statusUpdateQuestions},statusUpdateQuestionItem:function(t){return t.statusUpdateQuestionItem},statusUpdateQuestionAnswer:function(t){return t.statusUpdateQuestionAnswer}},f={SORT_QUESTIONS:function(t,e){t.questions=t.questions.map((function(t){return t.order=e[t.id],t}))},SORT_QUESTION_ANSWERS:function(t,e){t.questions=t.questions.map((function(t){return t.answers.answer_order=e[t.answers.question_answer_id],t}))},ADD_QUESTION_ANSWER:function(t,e){t.questions=t.questions.map((function(t){if(t.id===e.question_id){var n=!1;if(e.answer.temp_id)for(var i=0,o=t.answers.length;i<o;i++)t.answers[i].question_answer_id==e.answer.temp_id&&(n=!0,$Vue.set(t.answers,i,e.answer));return!n&&t.answers.push(e.answer),t}return t}))},CHANGE_QUESTION_CORRECT_ANSWERS:function(t,e){t.questions=t.questions.map((function(t){return parseInt(t.id)===e.id&&(t.answers=e.answers),t}))},SET_QUESTIONS:function(t,e){t.questions=e},ADD_NEW_QUESTION:function(t,e){var n=!1;if(e.temp_id)for(var i=0,o=t.questions.length;i<o;i++)if(t.questions[i].id===e.temp_id){$Vue.set(t.questions,i,e),n=!0;break}if(!n){var s=$(".lp-list-questions .main > div:last-child");if(s.length){var u=s.offset().top;$("html,body").animate({scrollTop:u})}t.questions.push(e)}},CHANGE_QUESTION_TYPE:function(t,e){t.questions=t.questions.map((function(t){return parseInt(t.id)===e.id&&(t.answers=e.answers,t.type=e.type,t.open=!0),t}))},REMOVE_QUESTION:function(t,e){var n=t.questions.indexOf(e);e.temp_id?t.questions[n].id=e.temp_id:t.questions.splice(n,1)},DELETE_QUESTION_ANSWER:function(t,e){var n=e.question_id,i=e.answer_id;t.questions=t.questions.map((function(t){if(t.id===n){var e=t.answers;e.forEach((function(t){if(t.question_answer_id===i){var n=e.indexOf(t);e.splice(n,1)}}))}return t}))},REMOVE_QUESTIONS:function(){},CLOSE_QUESTION:function(t,e){t.questions.forEach((function(n,i){e.id===n.id&&(t.questions[i].open=!1)}))},OPEN_QUESTION:function(t,e){t.questions.forEach((function(n,i){e.id===n.id&&(t.questions[i].open=!0)}))},CLOSE_LIST_QUESTIONS:function(t){t.questions=t.questions.map((function(t){return t.open=!1,t}))},OPEN_LIST_QUESTIONS:function(t){t.questions=t.questions.map((function(t){return t.open=!0,t}))},UPDATE_QUESTION_REQUEST:function(t,e){$Vue.set(t.statusUpdateQuestionItem,e,"updating")},UPDATE_QUESTION_SUCCESS:function(t,e){$Vue.set(t.statusUpdateQuestionItem,e,"successful")},UPDATE_QUESTION_FAILURE:function(t,e){$Vue.set(t.statusUpdateQuestionItem,e,"failed")},UPDATE_QUESTION_ANSWER_REQUEST:function(t,e){$Vue.set(t.statusUpdateQuestionAnswer,e,"updating")},UPDATE_QUESTION_ANSWER_SUCCESS:function(t,e){$Vue.set(t.statusUpdateQuestionAnswer,e,"successful")},UPDATE_QUESTION_ANSWER_FAIL:function(t,e){$Vue.set(t.statusUpdateQuestionAnswer,e,"failed")},DELETE_ANSWER:function(t,e){console.log("A"),t.questions.map((function(t,n){if(t.id==e.question_id){for(var i=0,o=t.answers.length;i<o;i++)if(t.answers[i].question_answer_id==e.answer_id){t.answers[i].question_answer_id=e.temp_id;break}return!1}}))}},m={toggleAll:function(t){t.getters.isHiddenListQuestions?t.commit("OPEN_LIST_QUESTIONS"):t.commit("CLOSE_LIST_QUESTIONS"),LP.Request({type:"hidden-questions",hidden:t.getters.hiddenQuestions})},updateQuizQuestionsHidden:function(t,e){LP.Request($.extend({},e,{type:"update-quiz-questions-hidden"}))},newQuestion:function(t,e){var n=JSON.parse(JSON.stringify(e.question));n.settings={},t.commit("ADD_NEW_QUESTION",n),LP.Request({type:"new-question",question:JSON.stringify(e.question),draft_quiz:JSON.stringify(e.quiz)}).then((function(n){var i=n.body;i.success&&(t.commit("UPDATE_NEW_QUESTION_TYPE",e.question.type,{root:!0}),t.commit("ADD_NEW_QUESTION",i.data),t.commit("CLOSE_LIST_QUESTIONS"),t.commit("OPEN_QUESTION",i.data))}),(function(t){console.log(t)}))},updateQuestionsOrder:function(t,e){LP.Request({type:"sort-questions",order:JSON.stringify(e)}).then((function(n){t.commit("SORT_QUESTIONS",e)}),(function(t){console.log(t)}))},updateQuestionTitle:function(t,e){t.commit("UPDATE_QUESTION_REQUEST",e.id),LP.Request({type:"update-question-title",question:JSON.stringify(e)}).then((function(){t.commit("UPDATE_QUESTION_SUCCESS",e.id)})).catch((function(){t.commit("UPDATE_QUESTION_FAILURE",e.id)}))},changeQuestionType:function(t,e){t.commit("UPDATE_QUESTION_REQUEST",e.question_id),LP.Request({type:"change-question-type",question_id:e.question_id,question_type:e.type}).then((function(n){var i=n.body;if(i.success){var o=i.data;t.commit("CHANGE_QUESTION_TYPE",o),t.commit("UPDATE_NEW_QUESTION_TYPE",o.type.key,{root:!0}),t.commit("UPDATE_QUESTION_SUCCESS",e.question_id)}})).catch((function(){t.commit("UPDATE_QUESTION_FAILURE",e.question_id)}))},isHiddenQuestionsSettings:function(t,e){},cloneQuestion:function(t,e){LP.Request({type:"clone-question",question:JSON.stringify(e)}).then((function(e){var n=e.body;if(n.success){var i=n.data;t.commit("ADD_NEW_QUESTION",n.data),t.commit("UPDATE_NEW_QUESTION_TYPE",i.type.key,{root:!0})}}),(function(t){console.log(t)}))},removeQuestion:function(t,e){var n=e.id;e.temp_id=LP.uniqueId(),t.commit("REMOVE_QUESTION",e),LP.Request({type:"remove-question",question_id:n}).then((function(n){n.body.success&&(e.id=e.temp_id,e.temp_id=0,t.commit("REMOVE_QUESTION",e))}),(function(t){console.error(t)}))},deleteQuestion:function(t,e){var n=e.id;e.temp_id=LP.uniqueId(),t.commit("REMOVE_QUESTION",e),LP.Request({type:"delete-question",question_id:n}).then((function(){e.id=e.temp_id,e.temp_id=0,t.commit("REMOVE_QUESTION",e),t.commit("UPDATE_QUESTION_SUCCESS",e.id)})).catch((function(){t.commit("UPDATE_QUESTION_FAILURE",e.id)}))},toggleQuestion:function(t,e){e.open?t.commit("CLOSE_QUESTION",e):t.commit("OPEN_QUESTION",e),LP.Request({type:"hidden-questions",hidden:t.getters.hiddenQuestions})},updateQuestionAnswersOrder:function(t,e){t.commit("UPDATE_QUESTION_REQUEST",e.question_id),LP.Request({type:"sort-question-answers",question_id:e.question_id,order:JSON.stringify(e.order)}).then((function(n){var i=n.body.data;t.commit("SORT_QUESTION_ANSWERS",i),t.commit("UPDATE_QUESTION_SUCCESS",e.question_id)}),(function(n){t.commit("UPDATE_QUESTION_FAILURE",e.question_id),console.log(n)}))},updateQuestionAnswerTitle:function(t,e){t.commit("UPDATE_QUESTION_REQUEST",e.question_id),LP.Request({type:"update-question-answer-title",question_id:parseInt(e.question_id),answer:JSON.stringify(e.answer)}).then((function(){t.commit("UPDATE_QUESTION_ANSWER_SUCCESS",parseInt(e.question_id)),t.commit("UPDATE_QUESTION_SUCCESS",e.question_id)})).catch((function(){t.commit("UPDATE_QUESTION_ANSWER_FAILURE",parseInt(e.question_id)),t.commit("UPDATE_QUESTION_FAILURE",e.question_id)}))},updateQuestionCorrectAnswer:function(t,e){t.commit("UPDATE_QUESTION_REQUEST",e.question_id),LP.Request({type:"change-question-correct-answer",question_id:e.question_id,correct:JSON.stringify(e.correct)}).then((function(n){var i=n.body;i.success&&(t.commit("CHANGE_QUESTION_CORRECT_ANSWERS",i.data),t.commit("UPDATE_QUESTION_SUCCESS",e.question_id))}),(function(n){t.commit("UPDATE_QUESTION_FAILURE",e.question_id),console.log(n)}))},deleteQuestionAnswer:function(t,e){e.temp_id=LP.uniqueId(),t.commit("DELETE_ANSWER",e),t.commit("UPDATE_QUESTION_REQUEST",e.question_id),LP.Request({type:"delete-question-answer",question_id:e.question_id,answer_id:e.answer_id}).then((function(n){n.body.success&&(t.commit("DELETE_QUESTION_ANSWER",{question_id:e.question_id,answer_id:e.temp_id}),t.commit("UPDATE_QUESTION_SUCCESS",e.question_id))}),(function(n){t.commit("UPDATE_QUESTION_FAILURE",e.question_id),console.log(n)}))},newQuestionAnswer:function(t,e){var n=LP.uniqueId(),i=e.question_id;t.commit("UPDATE_QUESTION_REQUEST",i),t.commit("ADD_QUESTION_ANSWER",{question_id:i,answer:{text:LP_Quiz_Store.getters["i18n/all"].new_option,question_answer_id:n}}),LP.Request({type:"new-question-answer",question_id:i,question_answer_id:n}).then((function(n){var o=n.body;if(o.success){var s=o.data;t.commit("ADD_QUESTION_ANSWER",{question_id:i,answer:s}),t.commit("UPDATE_QUESTION_SUCCESS",i),e.success&&setTimeout((function(){e.success.apply(e.context,[s])}),300)}}),(function(e){t.commit("UPDATE_QUESTION_FAILURE",i),console.error(e)}))},updateQuestionContent:function(t,e){t.commit("UPDATE_QUESTION_REQUEST",e.id),LP.Request({type:"update-question-content",question:JSON.stringify(e)}).then((function(){t.commit("UPDATE_QUESTION_SUCCESS",e.id)})).catch((function(){t.commit("UPDATE_QUESTION_FAILURE",e.id)}))},updateQuestionMeta:function(t,e){t.commit("UPDATE_QUESTION_REQUEST",e.question.id),LP.Request({type:"update-question-meta",question:JSON.stringify(e.question),meta_key:e.meta_key}).then((function(){t.commit("UPDATE_QUESTION_SUCCESS",e.question.id)})).catch((function(){t.commit("UPDATE_QUESTION_FAILURE",e.question.id)}))}},S=window.jQuery,T=function(t){var e=t.listQuestions;return{namespaced:!0,state:S.extend({statusUpdateQuestions:{},statusUpdateQuestionItem:{},statusUpdateQuestionAnswer:{},questions:e.questions.map((function(t){var n=e.hidden_questions.find((function(e){return parseInt(t.id)===parseInt(e)}));return t.open=!n,t}))},e),getters:_,mutations:f,actions:m}},U=window.jQuery,p=function(t){return{state:U.extend({status:"success",heartbeat:!0,countCurrentRequest:0},t.root),getters:i,mutations:o,actions:s,modules:{cqi:a(t),i18n:Object(E.a)(t.i18n),lqs:T(t)}}},q=n(3);window.$Vue=window.$Vue||Vue,window.$Vuex=window.$Vuex||Vuex,window.jQuery(document).ready((function(){window.LP_Quiz_Store=new $Vuex.Store(p(lp_quiz_editor)),Object(q.a)({ns:"LPListQuizQuestionsRequest",store:LP_Quiz_Store}),setTimeout((function(){window.LP_Quiz_Editor=new $Vue({el:"#admin-editor-lp_quiz",template:"<lp-quiz-editor></lp-quiz-editor>"})}),100)}))},3:function(t,e,n){"use strict";function i(t){var e=window.jQuery,n=Vue.http;t=e.extend({ns:"LPRequest",store:!1},t||{});var i=null;LP.Request=function(o){return i=e("#publishing-action"),o.id=t.store.getters.id,o.nonce=t.store.getters.nonce,o["lp-ajax"]=t.store.getters.action,o.code=t.store.getters.code,i.find("#publish").addClass("disabled"),i.find(".spinner").addClass("is-active"),i.addClass("code-"+o.code),n.post(t.store.getters.urlAjax,o,{emulateJSON:!0,params:{namespace:t.ns,code:o.code}})},n.interceptors.push((function(e,n){e.params.namespace===t.ns?(t.store.dispatch("newRequest"),n((function(n){jQuery.isPlainObject(n.body)||(n.body=LP.parseJSON(n.body)),n.body.success||!1?t.store.dispatch("requestCompleted","successful"):t.store.dispatch("requestCompleted","failed"),i.removeClass("code-"+e.params.code),i.attr("class")||(i.find("#publish").removeClass("disabled"),i.find(".spinner").removeClass("is-active"))}))):n()}))}n.d(e,"a",(function(){return i}))},8:function(t,e,n){"use strict";var i=window.jQuery;e.a=function(t){return{namespaced:!0,state:i.extend({},t),getters:{all:function(t){return t}}}}}});
//# sourceMappingURL=quiz.js.map