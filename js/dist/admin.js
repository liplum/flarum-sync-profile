(()=>{var e={n:l=>{var t=l&&l.__esModule?()=>l.default:()=>l;return e.d(t,{a:t}),t},d:(l,t)=>{for(var a in t)e.o(t,a)&&!e.o(l,a)&&Object.defineProperty(l,a,{enumerable:!0,get:t[a]})},o:(e,l)=>Object.prototype.hasOwnProperty.call(e,l)};(()=>{"use strict";const l=flarum.core.compat["admin/app"];var t=e.n(l);t().initializers.add("liplum/sync-profile",(function(){t().extensionData.for("liplum-sync-profile").registerSetting({setting:"liplum-sync-profile.sync_avatar",label:t().translator.trans("liplum-sync-profile.admin.labels.sync_avatar"),type:"boolean"}).registerSetting({setting:"liplum-sync-profile.ignored_avatar",label:t().translator.trans("liplum-sync-profile.admin.labels.ignored_avatar"),type:"text"}).registerSetting({setting:"liplum-sync-profile.stop_avatar_change",label:t().translator.trans("liplum-sync-profile.admin.labels.stop_avatar_change"),type:"boolean"}).registerSetting({setting:"liplum-sync-profile.sync_groups",label:t().translator.trans("liplum-sync-profile.admin.labels.sync_groups"),type:"boolean"}).registerSetting({setting:"liplum-sync-profile.sync_bio",label:t().translator.trans("liplum-sync-profile.admin.labels.sync_bio"),type:"boolean"}).registerSetting({setting:"liplum-sync-profile.stop_bio_change",label:t().translator.trans("liplum-sync-profile.admin.labels.stop_bio_change"),type:"boolean"}).registerSetting({setting:"liplum-sync-profile.sync_masquerade",label:t().translator.trans("liplum-sync-profile.admin.labels.sync_masquerade"),type:"boolean"})}))})(),module.exports={}})();
//# sourceMappingURL=admin.js.map