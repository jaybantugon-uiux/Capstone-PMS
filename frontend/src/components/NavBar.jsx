import React, { useState } from 'react';
import { Dropdown } from 'react-bootstrap';
import {Link} from 'react-router-dom';
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
    const [openMenu, setOpenMenu] = useState(false);
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
                <a href="#home">HOME</a>
                <a href="#projects">PROJECTS</a>
                <a href="#portfolio">PORTFOLIO</a>
                <a href="#reviews">REVIEWS</a>
                <a href="#contact">CONTACT US</a>
            </div>
            <div className="navbar-right-container">
                <div className="primary-search-bar">
                    <input type="text" placeholder="Search..." />
                </div>
                <Dropdown>
                    <Dropdown.Toggle variant="link" id="dropdown-basic" className="account-dropdown">
                        <AccountCircleIcon 
                            fontSize="large" 
                            sx={{ 
                                color: '#706F6F', 
                                '&:hover': { color: '#555' },
                                cursor: 'pointer'
                            }} 
                        />
                    </Dropdown.Toggle>

                    <Dropdown.Menu>
                        <Dropdown.Item as={Link} to="/signup">Login</Dropdown.Item>
                        <Dropdown.Item as={Link} to="/signup">Sign Up</Dropdown.Item>
                    </Dropdown.Menu>
                </Dropdown>
                <div className="navbar-menu-container">
                    <HiOutlineBars3 className="primary-menu-bar" onClick={() => setOpenMenu(true)}/>
                </div>
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