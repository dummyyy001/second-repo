import{l as P,j as T,ca as b,c9 as j,dp as h,b3 as o}from"./main-1.0.6.js";import{d as E}from"./dayjs.min-1.0.6-fcad92ca.js";import{u as O,a3 as y}from"./main-1.0.6-7aa8b30c.js";import"./vi-1.0.6-f3183323.js";import{L as U}from"./useMobilePicker-1.0.6-328fb89a.js";import{A as Y}from"./AdapterDayjs-1.0.6-3596f1a0.js";import{D as k}from"./DatePicker-1.0.6-75aa4c76.js";import"./iconBase-1.0.6-b8af29f5.js";import"./DialogContent-1.0.6-e7b9fcb3.js";import"./TextField-1.0.6-b41aa978.js";import"./Close-1.0.6-4b5eac8d.js";import"./InputAdornment-1.0.6-f8c6f282.js";import"./AdminTheme-1.0.6-6896de59.js";import"./dateViewRenderers-1.0.6-faae157e.js";const G=({appId:t,columnName:e,columnValue:p,columnMetaData:i,storeColumn:f,columnValidation:r,onColumnChange:d,metaData:L,storeTable:D,storeForm:g,formMode:x})=>{P.debug(t,e,p,i,f,r,L,D,g,x);const a=O(),A=()=>"en",F=()=>o.OUTLINED;return T.jsx(U,{dateAdapter:Y,adapterLocale:A(),children:T.jsx(k,{label:i.formLabel,value:p?E(p):null,disabled:x===y.VIEW||x===y.UPDATE&&L.primary_key.includes(e),onChange:s=>{s!==null&&!E(s).isValid()?a(b({appId:t,columnName:e,columnError:!0,columnText:"Invalid date",columnType:j.COLUMN})):(s===null?d(e,null):d(e,E(s).format("YYYY-MM-DD")),a(b({appId:t,columnName:e,columnError:!1,columnText:"",columnType:j.COLUMN})))},slotProps:{textField:{error:r==null?void 0:r.error,helperText:r!=null&&r.error?r==null?void 0:r.text:h(f,"Enter a date"),variant:F(),required:i.is_nullable==="NO",inputProps:{className:f.classNames}}}})})};export{G as default};