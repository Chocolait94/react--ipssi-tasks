import { Link } from "react-router";
import styles from "./navlink.module.css";

const NavLink = ({ path, children }) => {
  return (
    <Link
      className={location.pathname === path ? styles.actual : styles.link}
      to={path}
    >
      {children}
    </Link>
  );
};

export default NavLink;
