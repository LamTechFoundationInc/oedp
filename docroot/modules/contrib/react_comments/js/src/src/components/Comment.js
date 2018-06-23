import React, { Component } from 'react';
import CommentBox from './CommentBox';
import './Comment.css';
import timeago from 'timeago.js';
import Constants from '../utils/constants';
import ReplyArrow from '../icons/reply-arrow';
import DeletedComment from './DeletedComment';
import ReplyList from './ReplyList';
import CommentActions from './CommentActions';
import CommentMenu from './CommentMenu';

class Comment extends Component {
  state = {
    replyActive: false,
    editActive: false,
    message: null
  };

  toggleState = (stateToToggle) => {
    this.setState({[stateToToggle]: !this.state[stateToToggle]});
  };

  postReply = (commentText, anonName) => {
    return this.props.postReply(this.props.id, commentText, anonName);
  };

  saveEdit = (commentText) => {
    return this.props.saveEdit(this.props.id, commentText);
  };

  deleteComment = (id) => {
    return this.props.deleteComment(id).catch((err) => {
      this.setState({
        message: {
          message: err.message,
          type: 'error'
        }
      })
    });
  };

  closeMenu = () => {
    if (this.props.id === this.props.openMenu) {
      this.props.toggleMenu(this.props.id);
    }
  };

  convertTimestampToDate = (created_at) => {
    const date = new Date(created_at * 1000);

    // if the comment was posted more than a week ago show the date instead
    if (Date.now() - created_at * 1000 > 604800000) {
      return `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`;
    }
    return timeago().format(created_at * 1000)
  };

  render() {
    const { id, currentUser, comment, replies, replyTo, created_at, openMenu, toggleMenu, status, flagComment, publishComment, unpublishComment, published, name} = this.props;
    const { replyActive, editActive, message } = this.state;

    let { user } = this.props;

    if (user.isAnon) {
      user.name = name;
    }

    if (status === Constants.deleted || status === Constants.flaggedUnpublished) {
      return (
          <DeletedComment {...this.props} />
      );
    }

    return (
      <div className={editActive ? "rc_comment rc_comment--edit-active" : "rc_comment"}>
        <div className={ (published === '0') ? "rc_comment-container rc_comment-container--unpublished" : "rc_comment-container" }>
          <div className="rc_avatar">
            <img alt="User avatar" src={user.thumbnail}/>
          </div>
          <div className="rc_body">
            { message && <div className={`rc_message rc_message-type--${message.type}`}>{message.message}</div> }
            <div className="rc_comment-details">
              <span className="rc_username">{user.name}</span>
              { replyTo && <span className="rc_reply-to"><ReplyArrow/>{replyTo.name}</span> }
              {/* Apparently javascript uses 13 digit timestamps (including milliseconds)... Append 000 to the unix timestamp to get it to work. */}
              <span className="rc_time-ago">{this.convertTimestampToDate(created_at)}</span>

              <CommentMenu
                  user={user}
                  currentUser={currentUser}
                  id={id}
                  openMenu={openMenu}
                  closeMenu={this.closeMenu}
                  toggleMenu={toggleMenu}
                  flagComment={flagComment}
                  publishComment={publishComment}
                  unpublishComment={unpublishComment}
                  deleteComment={this.deleteComment}
                  status={status}
                  published={published}
              />
            </div>

            { editActive ?
              <CommentBox
                commentId={id}
                text={comment}
                user={currentUser}
                isEdit={true}
                type="edit"
                cancelEdit={() => this.toggleState('editActive')}
                saveEdit={this.saveEdit}
              /> :
              <div className="rc_comment-text" dangerouslySetInnerHTML={{__html: comment.replace(/(?:\r\n|\r|\n)/g, '<br />')}}></div>
            }

            <CommentActions
              currentUser={currentUser}
              user={user}
              editActive={editActive}
              replyActive={replyActive}
              toggleState={this.toggleState}
            />

            { replyActive &&
              <CommentBox
                commentId={id}
                isReply={true}
                type="reply"
                user={ currentUser }
                postReply={this.postReply}
                closeReplyBox={() => this.toggleState('replyActive')}
              /> }

          </div>
        </div>
        { replies &&
          <ReplyList
              {...this.props}
              replyTo={user}
          />
        }
      </div>
    );
  }
}

export default Comment;
