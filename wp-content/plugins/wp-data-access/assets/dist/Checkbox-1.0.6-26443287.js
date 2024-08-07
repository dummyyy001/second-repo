import{a as P,g as y,s as B,V as g,A as l,_ as n,K as M,e as S,b as _,h as R,f as H}from"./iconBase-1.0.6-b8af29f5.js";import{j as t,r as m}from"./main-1.0.6.js";import{a as V}from"./FormControlLabel-1.0.6-72a72881.js";import{c as h}from"./Close-1.0.6-4b5eac8d.js";const E=h(t.jsx("path",{d:"M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"}),"CheckBoxOutlineBlank"),O=h(t.jsx("path",{d:"M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.11 0 2-.9 2-2V5c0-1.1-.89-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"}),"CheckBox"),U=h(t.jsx("path",{d:"M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2z"}),"IndeterminateCheckBox");function L(o){return y("MuiCheckbox",o)}const N=P("MuiCheckbox",["root","checked","disabled","indeterminate","colorPrimary","colorSecondary","sizeSmall","sizeMedium"]),u=N,F=["checkedIcon","color","icon","indeterminate","indeterminateIcon","inputProps","size","className"],w=o=>{const{classes:e,indeterminate:c,color:a,size:r}=o,s={root:["root",c&&"indeterminate",`color${l(a)}`,`size${l(r)}`]},d=H(s,L,e);return n({},e,d)},A=B(V,{shouldForwardProp:o=>g(o)||o==="classes",name:"MuiCheckbox",slot:"Root",overridesResolver:(o,e)=>{const{ownerState:c}=o;return[e.root,c.indeterminate&&e.indeterminate,e[`size${l(c.size)}`],c.color!=="default"&&e[`color${l(c.color)}`]]}})(({theme:o,ownerState:e})=>n({color:(o.vars||o).palette.text.secondary},!e.disableRipple&&{"&:hover":{backgroundColor:o.vars?`rgba(${e.color==="default"?o.vars.palette.action.activeChannel:o.vars.palette[e.color].mainChannel} / ${o.vars.palette.action.hoverOpacity})`:M(e.color==="default"?o.palette.action.active:o.palette[e.color].main,o.palette.action.hoverOpacity),"@media (hover: none)":{backgroundColor:"transparent"}}},e.color!=="default"&&{[`&.${u.checked}, &.${u.indeterminate}`]:{color:(o.vars||o).palette[e.color].main},[`&.${u.disabled}`]:{color:(o.vars||o).palette.action.disabled}})),K=t.jsx(O,{}),T=t.jsx(E,{}),W=t.jsx(U,{}),q=m.forwardRef(function(e,c){var a,r;const s=S({props:e,name:"MuiCheckbox"}),{checkedIcon:d=K,color:b="primary",icon:z=T,indeterminate:i=!1,indeterminateIcon:x=W,inputProps:I,size:p="medium",className:$}=s,j=_(s,F),C=i?x:z,k=i?x:d,f=n({},s,{color:b,indeterminate:i,size:p}),v=w(f);return t.jsx(A,n({type:"checkbox",inputProps:n({"data-indeterminate":i},I),icon:m.cloneElement(C,{fontSize:(a=C.props.fontSize)!=null?a:p}),checkedIcon:m.cloneElement(k,{fontSize:(r=k.props.fontSize)!=null?r:p}),ownerState:f,ref:c,className:R(v.root,$)},j,{classes:v}))}),X=q;export{X as C};
