import{l as g,r as l,j as e}from"./main-1.0.6.js";import{a5 as j}from"./main-1.0.6-7aa8b30c.js";import{C}from"./settings-1.0.6-c1644694.js";import{k as M,g as b}from"./index.esm-1.0.6-d540f227.js";import{I as D}from"./iconBase-1.0.6-b8af29f5.js";import{M as E}from"./TextField-1.0.6-b41aa978.js";import{M as a}from"./MenuItem-1.0.6-b9a91535.js";import"./FormControlLabel-1.0.6-72a72881.js";import"./Close-1.0.6-4b5eac8d.js";import"./DialogContent-1.0.6-e7b9fcb3.js";import"./AdminTheme-1.0.6-6896de59.js";import"./Tabs-1.0.6-e49ea330.js";const T=({index:r,computedField:p,prepareComputedFieldUpdate:m,deleteComputedField:c})=>{g.debug(r,p);const[i,t]=l.useState(null),n=!!i,d=o=>{t(o.currentTarget),o.stopPropagation()},u=()=>{t(null)},f=o=>{m(r),t(null),o.stopPropagation()},h=o=>{s(!0),t(null),o.stopPropagation()},[x,s]=l.useState(!1);return e.jsxs("div",{children:[e.jsx(D,{id:"pp-computed-field-more-button","aria-controls":n?"pp-computed-field-more-menu":void 0,"aria-haspopup":"true","aria-expanded":n?"true":void 0,onClick:d,children:e.jsx(j,{})}),e.jsxs(E,{id:"pp-computed-field-more-menu",anchorEl:i,open:n,onClose:u,MenuListProps:{"aria-labelledby":"pp-computed-field-more-button"},sx:{zIndex:9999999999},anchorOrigin:{vertical:"top",horizontal:"left"},transformOrigin:{vertical:"top",horizontal:"left"},children:[e.jsxs(a,{onClick:f,children:[e.jsx(M,{}),e.jsx("span",{style:{marginLeft:"10px"},children:"Edit"})]}),e.jsxs(a,{onClick:h,children:[e.jsx(b,{}),e.jsx("span",{style:{marginLeft:"10px"},children:"Delete"})]})]}),e.jsx(C,{title:"Delete computed field?",message:"Are you sure you want to delete this computed field? This action cannot be undone!",open:x,setOpen:s,onConfirm:()=>c(r)})]})};export{T as default};
