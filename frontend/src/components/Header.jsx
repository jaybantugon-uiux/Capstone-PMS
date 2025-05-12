import { FaUserCircle } from "react-icons/fa";

function Header() {
    return (
        <div className="container-fluid">
            <nav className="navbar">
                <a href="#" className="navbar-img">
                    <img 
                        src="./src/assets/druLogoRaw.jpg" 
                        className="navbar-logo" 
                        alt="Designs R' Us Logo" 
                    />
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                    <ul className="nav-links">
                        <li><a href="#" className="nav-link">
                            <span className="nav-link-text">Home</span>
                        </a></li>
                        <li><a href="#" className="nav-link">
                            <span className="nav-link-text">Projects</span>
                        </a></li>
                        <li><a href="#" className="nav-link">
                            <span className="nav-link-text">Portfolio</span>
                        </a></li>
                        <li><a href="#" className="nav-link">
                            <span className="nav-link-text">Reviews</span>
                        </a></li>
                        <li><a href="#" className="nav-link">
                            <span className="nav-link-text">Contact Us</span>
                        </a></li>
                    </ul>

                    <form className="search-form" role="search">
                        <input 
                            type="search" 
                            className="form-control me-2" 
                            placeholder="Search" 
                        />
                    </form>

                    <FaUserCircle className="user-icon" style={{ fontSize: '2rem', color: '#6c757d' }} />
                </div>
            </nav>
        </div>
    );
}

export default Header;