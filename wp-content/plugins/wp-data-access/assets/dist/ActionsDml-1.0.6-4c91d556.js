import{l as r,C as i,J as u,cf as d}from"./main-1.0.6.js";import{R as n}from"./main-1.0.6-7aa8b30c.js";const b=e=>{r.debug(e);const a=d(e);return r.debug(a),{...a}},k=(e,a,o,s,t,p=!1)=>{r.debug(e,a),n(u.appUrlMeta,{dbs:e,tbl:a,waa:o},s,t,p)},A=(e,a,o,s,t,p=!1,f=!1)=>{r.debug(e,a,o);const l=b(e);l===!1?(r.error("error","Missing data source"),t(i.contactSupport)):(l.key=a,l.media=o,f&&(l.rel_tab=!0),n(l.app_id?u.appUrlAppGet:u.appUrlGet,l,c=>{var g;r.debug(c),((g=c==null?void 0:c.data)==null?void 0:g.length)===1?s(c):s(null)},t,p))},M=(e,a,o,s,t=!1,p=!1,f=!1)=>{r.debug(e,a);const l=b(e);l===!1?(r.error("error","Missing data source"),s(i.contactSupport)):(l.val=a,p&&(l.join_tab=!0),f&&(l.rel_tab=!0),n(l.app_id?u.appUrlAppInsert:u.appUrlInsert,l,o,s,t))},S=(e,a,o,s,t,p=!1,f=!1,l=!1)=>{r.debug(e,a,o);const c=b(e);c===!1?(r.error("error","Missing data source"),t(i.contactSupport)):(c.key=a,c.val=o,f&&(c.join_tab=!0),l&&(c.rel_tab=!0),n(c.app_id?u.appUrlAppUpdate:u.appUrlUpdate,c,s,t,p))},R=(e,a,o,s,t=!1)=>{r.debug(e,a);const p=b(e);p===!1?(r.error("error","Missing data source"),s(i.contactSupport)):(p.key=a,n(p.app_id?u.appUrlAppDelete:u.appUrlDelete,p,o,s,t))},C=(e,a,o,s,t,p,f,l,c=!1)=>{r.debug(e,a,o,s,t,p);const g=b(e);g===!1?(r.error("error","Missing data source"),l(i.contactSupport)):(g.target=a,g.col=o,g.colk=s,g.colv=t,g.cold=p,n(u.appUrlAppLookup,g,f,l,c))},D=(e,a,o,s=!1)=>{r.debug(e);const t=b(e);t===!1?(r.error("error","Missing data source"),o(i.contactSupport)):n(u.appUrlAppLookupDbs,t,a,o,s)},w=(e,a,o,s,t=!1)=>{r.debug(e);const p=b(e);p===!1?(r.error("error","Missing data source"),s(i.contactSupport)):(p.dbs=a,n(u.appUrlAppLookupTbl,p,o,s,t))},L=(e,a,o,s,t,p=!1)=>{r.debug(e);const f=b(e);f===!1?(r.error("error","Missing data source"),t(i.contactSupport)):(f.dbs=a,f.tbl=o,n(u.appUrlAppLookupCls,f,s,t,p))};export{w as a,L as b,D as c,R as d,k as g,M as i,C as l,A as s,S as u};