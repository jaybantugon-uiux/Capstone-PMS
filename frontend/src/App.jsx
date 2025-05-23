import './App.css';
import Home from './pages/Home.jsx';
import SignUp from './pages/SignUp';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

function App() {
  return (
    <Router>
      <div className="home-container">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/signup" element={<SignUp />} />
          {}
        </Routes>
        <div className="home-banner-container">
          <div className="home-bannerImage-container">

          </div>
        </div>
      </div>
    </Router>
  );
}

export default App;
