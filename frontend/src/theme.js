// src/theme.ts
export const themeOverrides = {
  common: {
    primaryColor: '#2F80ED',
    primaryColorHover: '#5393f9',
    primaryColorPressed: '#1c6dd0',
    primaryColorSuppl: '#2F80ED'
  },
 Button: {
    colorPrimary: '#2F80ED',
    colorHoverPrimary: '#5393f9',
    colorPressedPrimary: '#1c6dd0',
    textColorPrimary: '#ffffff',        
    heightMedium: '48px',         
    borderRadiusMedium: '12px',   
    fontWeight: '600',            
    transitionDuration: '.2s',     
    rippleDuration: '.3s'       
  },
    Input: {
    heightMedium: '48px',           
    borderRadius: '12px',           
    fontSizeMedium: '14px',
    paddingMedium: '12px 14px'       
  },
  Tag: {
    colorPrimary: '#2F80ED',
    textColorPrimary: '#ffffff'
  },
  Checkbox: {
    colorChecked: '#2F80ED'
  },
  Radio: {
    colorChecked: '#2F80ED',
   buttonBorderRadius: '12px',
    buttonHeightMedium: '48px'
  },
  Pagination: {              
    itemBorderRadius: '8px', 
  },
  Select: {
    peers: {
      InternalSelection: {
        borderRadius: '12px',
        heightMedium: '48px',
        paddingSingle: '0 14px',
        fontSizeMedium: '14px',
      },
      InternalSelectMenu: {
        borderRadius: '8px',
      }
    }
  },
   Dialog: {
    borderRadius: '12px'
  },
}
