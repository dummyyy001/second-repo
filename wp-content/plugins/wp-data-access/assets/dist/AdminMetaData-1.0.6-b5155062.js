import{l as s,r as n,aB as h,aL as D,j as u,C as g}from"./main-1.0.6.js";import{u as j,q as L,r as M,s as T,B as y,S as B}from"./main-1.0.6-7aa8b30c.js";import{g as C}from"./ActionsDml-1.0.6-4c91d556.js";const v=({dbs:e,tbl:r,appId:c,exploring:p})=>{s.debug(e,r,c);const m=j(),[d,i]=n.useState(""),[f,S]=n.useState(!1),{prepareAdminStore:A}=L(c,M.ADMIN);n.useEffect(()=>{f||E()},[e,r]);const E=()=>{C(e,r,!1,function(a){var l,x;const t=a==null?void 0:a.data;if(s.debug("response data",e,r,t),(l=t==null?void 0:t.access)!=null&&l.select&&Array.isArray(t.access.select)&&t.access.select.includes("POST"))A(e,r,t,p===!0)?S(!0):i(g.contactSupport);else{let o="Unauthorized";((x=a==null?void 0:a.data)==null?void 0:x.message)!==void 0&&(o=a.data.message),o+=" - check console for more information",s.error(o),i(o)}},a=>{s.error("error",a),i(a??g.contactSupport)})};return d!==""?(m(h({error:d})),m(D({})),null):f?u.jsx(T,{appId:c}):u.jsx(y,{sx:{padding:"30px"},children:u.jsx(B,{title:"Loading meta data..."})})};export{v as A};
