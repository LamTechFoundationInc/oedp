import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Constants from '../utils/constants';
import Caret from '../icons/caret-down';

class CommentMenu extends Component {

    documentClick = (e) => {
        const menu = ReactDOM.findDOMNode(this.refs.menu);
        const toggle = ReactDOM.findDOMNode(this.refs.toggle);

        if (menu && toggle && !menu.contains(e.target) && this.props.id === this.props.openMenu && !toggle.contains(e.target)) {
            this.props.toggleMenu(this.props.id);
        }
    };

    componentWillMount() {
        document.addEventListener('click', this.documentClick, false);
    }

    componentWillUnmount() {
        document.removeEventListener('click', this.documentClick, false);
    }

    render() {
        const {user, currentUser, id, openMenu, closeMenu, toggleMenu, flagComment, deleteComment, publishComment, unpublishComment, status, published} = this.props;
        const userCanDeleteComment = currentUser && !currentUser.isAnon && currentUser.hasPermission('edit own comments') && (user.id === currentUser.id);
        const userCanAdministerComments = currentUser && currentUser.hasPermission('administer comments');

        return (
            <div className="rc_comment-menu-wrapper">
                <div ref="toggle"
                     className="rc_comment-menu-toggle"
                     onClick={(e) => { e.preventDefault(); toggleMenu(id);}}><Caret /></div>
                <ul ref="menu"
                    className={(id === openMenu) ? 'rc_comment-menu rc_comment-menu--active' : 'rc_comment-menu'} >
                    { (userCanAdministerComments && (published === '0')) && <li onClick={() => {publishComment(id); closeMenu();}}>Publish</li> }
                    { (userCanAdministerComments && (published === '1')) && <li onClick={() => {unpublishComment(id); closeMenu();}}>Unpublish</li> }
                    { (userCanAdministerComments || userCanDeleteComment) && <li onClick={() => {deleteComment(id); closeMenu();}}>Delete</li> }
                    { status !== Constants.flagged ? <li onClick={() => {flagComment(id); closeMenu();}}>Flag as inappropriate</li> : <li>Flagged</li> }
                </ul>
            </div>
        );
    }

}

export default CommentMenu;