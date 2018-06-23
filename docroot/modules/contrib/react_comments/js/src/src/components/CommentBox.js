import React, { Component } from 'react';
import { Editor, EditorState, ContentState } from 'draft-js';
import './CommentBox.css';
import Throbber from 'react-throbber';
import 'react-throbber/src/style.css';
import Constants from '../utils/constants';

class CommentBox extends Component {
  state = {
    isOpen: false,
    isFocused: false,
    isLoading: false,
    message: false,
    messageOnly: false,
    anonFormValue: false,
    editorState: (this.props.type === 'edit') ?
      EditorState.createWithContent(ContentState.createFromText(this.props.text)) :
      EditorState.createEmpty()
  };

  getLoginLink = () => {
    return `/user/login?destination=${encodeURIComponent(window.location.pathname)}%23comments-app-container`;
  };

  toggleFocus = () => {
    this.setState({
      isFocused: !this.state.isFocused,
      isOpen: true
    });
  };

  getCommentText = () => this.state.editorState.getCurrentContent().getPlainText();

  postComment = (commentText) => {
    if (this.state.isLoading) return;

    if (!this.userCanPost()) return;

    this.setState({ isLoading: true, message: false });
    this.props.postComment(commentText, this.state.anonFormValue).then((response) => {

      if (response.code === 'success') {
        this.setState({
          isLoading: false,
          editorState: EditorState.createEmpty(),
        });
      }
      else if (response.code === 'queued_for_moderation') {
        this.setState({
          message: {
            message: response.message,
            type: 'success'
          },
          isLoading: false,
          editorState: EditorState.createEmpty(),
        });
      }
    }).catch((error) => {
      const message = {
        message: error.message,
        type: 'error'
      };
      this.setState({ isLoading: false, message: message });
    });
  };

  postReply = (commentText) => {
    if (this.state.isLoading) return;

    if (!this.userCanPost()) return;

    this.setState({ isLoading: true, message: false });
    this.props.postReply(commentText, this.state.anonFormValue).then((response) => {
      if (response.code === 'success') {
        this.setState({ isOpen: false, message: false });
        this.props.closeReplyBox();
      }
      else if (response.code === 'queued_for_moderation') {
        const message = {
          message: response.message,
          type: 'success'
        };
        this.setState({ message: message, messageOnly: true, isLoading: false });
      }
    }).catch((error) => {
      const message = {
        message: error.message,
        type: 'error'
      };
      this.setState({ isLoading: false, message: message });
    });
  };

  saveEdit = (commentText) => {
    if (this.state.isLoading) return;
    
    if (!this.userCanPost()) return;

    this.setState({ isLoading: true, message: false });
    this.props.saveEdit(commentText).then(() => {
      this.props.cancelEdit();
    }).catch((error) => {
      const message = {
        message: error.message,
        type: 'error'
      };
      this.setState({ isLoading: false, message: message });
    });
  };

  userCanPost = () => {
    if (this.props.user && this.props.user.isAnon && !this.state.anonFormValue) {
      this.setState({ message: { message: 'Please provide your name or alias to post as a guest', type: 'error'}});
      return false;
    }
    else {
      return true;
    }
  };

  handleAnonFormChange = (e) => {
    if (e.target.value) {
      this.setState({anonFormValue: e.target.value});
    }
    else {
      this.setState({anonFormValue: false});
    }
  };

  componentDidMount() {
    if (this.props.user && this.props.type === 'reply') {
      const x = window.scrollX;
      const y = window.scrollY;
      this.commentBox.focus();
      window.scrollTo(x, y);
    }
  }

  render() {
    const { user, type, cancelEdit } = this.props;
    const { isOpen, isFocused, isLoading, message, messageOnly, editorState } = this.state;

    let containerClasses = ['rc_comment-box-container'];
    if (isOpen || type === 'reply' || type === 'edit' ) containerClasses.push('rc_is-open');
    if (isLoading) containerClasses.push('rc_is-loading');
    if (messageOnly) containerClasses.push('rc_message-only');
    if (type === 'reply') containerClasses.push('rc_is-reply');
    if (type === 'edit') containerClasses.push('rc_is-edit');
    containerClasses = containerClasses.join(' ');

    return (
      <div className={containerClasses}>
        { type !== 'edit' &&
          <div className='rc_comment-box-avatar'>
            <img alt="User avatar" src={ (user && user.thumbnail) || Constants.defaultAvatarUrl }/>
          </div>
        }
        <div className="rc_input-outer-wrapper">
          { message && <div className={`rc_message rc_message-type--${message.type}`}>{message.message}</div> }
          <div className="rc_throbber-wrapper">
            { isLoading && <Throbber size="25px"/> }
            <div className="rc_input-wrapper">
              <Editor
                  placeholder={ !isFocused && (type !== 'edit') ? 'Join the discussion...' : '' }
                  editorState={ editorState }
                  onChange={(editorState) => this.setState({ editorState })}
                  ref={(commentBox) => this.commentBox = commentBox}
                  onFocus={this.toggleFocus}
                  onBlur={this.toggleFocus}
              />
              <div className="rc_input-actions">
                { type === 'edit' &&
                <span>
                <button onClick={() => cancelEdit()} className="rc_cancel-comment">Cancel</button>
                <button onClick={() => this.saveEdit(this.getCommentText())} className="rc_add-comment">Save Edit</button>
              </span>
                }
                { (type === 'reply' && user) && <button onClick={() => this.postReply(this.getCommentText()) } className="rc_add-comment">Post{user.name && ` as ${user.name}`}</button> }
                { (type === 'comment' && user) && <button onClick={() => this.postComment(this.getCommentText())} className="rc_add-comment">Post{user.name && ` as ${user.name}`}</button> }
              </div>
            </div>
          </div>

          { (!user || user.isAnon) && <div className="rc_anon-wrapper">
            <div>
              <label htmlFor="rc_login-button">log in to comment</label>
              <a id="rc_login-button" className="rc_login-button" href={this.getLoginLink()}>Log in</a>
            </div>
            { (user && user.isAnon) && <div className="rc_anon-form">
              <div>
                <label htmlFor="rc_name">or post as a guest</label>
                <input onChange={this.handleAnonFormChange} id="rc_name" type="text" placeholder="Name"/>
              </div>
            </div> }
          </div> }
        </div>

      </div>
    );
  }
}

export default CommentBox;