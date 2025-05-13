import React, { useState } from 'react';
import Logo from "../assets/druLogo.png";
import { Box, Drawer, List, ListItem, ListItemButton, ListItemIcon, ListItemText } from "@mui/material";
import HomeIcon from '@mui/icons-material/Home';
import BrushIcon from '@mui/icons-material/Brush';
import DocumentScannerIcon from '@mui/icons-material/DocumentScanner';
import ReviewsIcon from '@mui/icons-material/Reviews';
import ContactsIcon from '@mui/icons-material/Contacts';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';
import { HiOutlineBars3 } from "react-icons/hi2";

function NavBar() {
    const [openMenu, setOpenMenu] = useState(false)
    const menuOptions = [
        {
            text: "Home",
            icon: <HomeIcon />,
        },
        {
            text: "Projects",
            icon: <BrushIcon />,
        },
        {
            text: "Portfolio",
            icon: <DocumentScannerIcon />,
        },
        {
            text: "Reviews",
            icon: <ReviewsIcon />,
        },
        {
            text: "Contact Us",
            icon: <ContactsIcon />,
        },
        {
            text: "Login/Sign Up",
            icon: <AccountCircleIcon />,
        },
    ]
    return (
        <nav>
            <div className="nav-logo-container">
                <img src={Logo} alt="Logo" />
            </div>
            <div className="navbar-links-container">
                <a href="#home">Home</a>
                <a href="#projects">Projects</a>
                <a href="#home">Portfolio</a>
                <a href="#home">Reviews</a>
                <a href="#home">Contact Us</a>
            </div>
            <div className="navbar-right-container">
                <input 
                    className="primary-search-bar"
                    type="search" 
                    placeholder="Search" 
                />
                <div className="user-icon-container">
                    <AccountCircleIcon 
                        fontSize="small" 
                        sx={{ color: 'inherit', '&:hover': { color: '#555' } }} 
                    />
                </div>
            </div>
            <div className="navbar-menu-container">
                <HiOutlineBars3 onClick={() => setOpenMenu(true)}/>
            </div>

            <Drawer open={openMenu} onClose={() => setOpenMenu(false)} anchor="right">
                <Box sx={{ width: 250 }}
                    role="presentation"
                    onClick={() => setOpenMenu(false)}
                    onKeyDown={() => setOpenMenu(false)}
                >
                    <List>
                        {menuOptions.map((item) => (
                            <ListItem key={item.text} disablePadding>
                                <ListItemButton>
                                    <ListItemIcon>{item.icon}</ListItemIcon>
                                    <ListItemText primary={item.text} />
                                </ListItemButton>
                            </ListItem>
                        ))}
                    </List>
                </Box>
            </Drawer>
        </nav>
    )
}

export default NavBar;