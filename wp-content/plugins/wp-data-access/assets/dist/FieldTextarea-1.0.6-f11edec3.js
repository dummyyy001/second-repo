import{l as R,j,dp as y,b3 as F}from"./main-1.0.6.js";import{a3 as x}from"./main-1.0.6-7aa8b30c.js";import{T as I}from"./TextField-1.0.6-b41aa978.js";import"./iconBase-1.0.6-b8af29f5.js";import"./Close-1.0.6-4b5eac8d.js";const N=({columnName:p,columnValue:T,columnInitialValue:h,columnMetaData:r,storeColumn:g,columnValidation:e,onColumnChange:b,metaData:v,storeForm:E,formMode:s})=>{R.debug(p,T,h,r,g,e,v,E,s);const O={maxLength:r.character_maximum_length,className:g.classNames,readOnly:s===x.VIEW||s===x.UPDATE&&v.primary_key.includes(p)},U=e!=null&&e.error?e==null?void 0:e.text:"Enter multi line text ("+r.character_maximum_length+")",f=()=>F.OUTLINED;return j.jsx(I,{error:e==null?void 0:e.error,id:p,label:r.formLabel,value:T??"",required:r.is_nullable==="NO",multiline:!0,minRows:3,maxRows:10,inputProps:O,helperText:y(g,U),variant:f(),onChange:t=>{let _=t.target.value;t.target.value===""&&(s===x.INSERT||s===x.UPDATE&&E.preserveSpacesOnUpdate===!1||E.preserveSpacesOnUpdate===!0&&h!=="")&&(_=null),b(p,_)},onInvalid:t=>{t.preventDefault()}})};export{N as default};
